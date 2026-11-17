<?php

namespace App\Http\Controllers;

use App\Models\BlastLog;
use App\Models\BlastRule;
use App\Models\Brand;
use App\Models\Content;
use App\Models\CustomerEmail;
use App\Services\SmtpBlastService;
use Illuminate\Http\Request;

class BlastController extends Controller
{
    public function index(Brand $brand)
    {
        $brand->load([
            'service.server',
            'activeBlastRules.emailHost.server',
            'activeBlastRules.activeDetails.relay.emailHost',
            'activeBlastRules.activeRecipients',
        ]);

        $contents = Content::where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->orderBy('date_namespace', 'desc')
            ->orderBy('time_namespace', 'desc')
            ->get();

        $recentLogs = BlastLog::with('content')
            ->where('brand_id', $brand->id)
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('blast.index', compact('brand', 'contents', 'recentLogs'));
    }

    /**
     * Memuat daftar penerima berdasarkan Rule yang dipilih (Test vs Blast).
     * Password SMTP sama sekali tidak diekspos ke browser.
     */
    public function loadRecipients(Request $request, Brand $brand)
    {
        $request->validate([
            'rule_id'  => 'required|integer',
            'simulate' => 'nullable|boolean',
        ]);

        $rule = BlastRule::with(['activeDetails.relay.emailHost', 'activeRecipients'])
            ->where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->findOrFail($request->input('rule_id'));

        $activeRelaysCount = $rule->activeDetails->filter(function ($detail) {
            return $detail->relay && $detail->relay->flag === 'Y';
        })->count();

        if ($activeRelaysCount === 0) {
            return response()->json([
                'code'    => 422,
                'message' => 'Rule "' . $rule->name . '" belum memiliki akun SMTP Relay aktif. Silakan atur pada menu Blast Rules.',
            ], 422);
        }

        if ($rule->type === 'test') {
            $recipients = $rule->activeRecipients->map(function ($r) {
                return [
                    'id'    => $r->id,
                    'email' => $r->email,
                    'name'  => $r->name ?: '-',
                ];
            })->values();

            return response()->json([
                'code'        => 200,
                'rule_type'   => 'test',
                'rule_name'   => $rule->name,
                'relay_count' => $activeRelaysCount,
                'data'        => $recipients,
            ]);
        }

        // Untuk tipe 'blast', periksa kesiapan interval waktu tunggu kecuali jika mode simulasi/staging
        $isStagingAccount = session('cosmosender_user.mode') === 'staging';
        $isSimulate = $isStagingAccount || (bool) $request->input('simulate', false);

        if (!$isSimulate && !$brand->isReadyToBlast()) {
            $waitMinutes = $brand->remainingWaitMinutes();
            return response()->json([
                'code'    => 422,
                'message' => "Belum memenuhi jeda waktu tunggu setelah blast terakhir ({$brand->blast_hour_interval} jam). Sisa waktu tunggu sekitar {$waitMinutes} menit.",
            ], 422);
        }

        $limit = (int) $brand->blast_limit;
        $lastEmailId = (int) $brand->last_email_id;

        $query = CustomerEmail::where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->orderBy('id', 'asc');

        if ($lastEmailId > 0 && $limit > 0) {
            $query->where('id', '>', $lastEmailId);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $customers = $query->get();

        // Wrap-around ke awal daftar jika pointer last_email_id sudah mencapai akhir tabel
        if ($customers->isEmpty()) {
            $fallbackQuery = CustomerEmail::where('brand_id', $brand->id)
                ->where('flag', 'Y')
                ->orderBy('id', 'asc');

            if ($limit > 0) {
                $fallbackQuery->limit($limit);
            }
            $customers = $fallbackQuery->get();
        }

        // Bulk update status_blast = 'ready' hanya dalam 1 query (bukan ribuan query di dalam loop)
        if ($customers->isNotEmpty() && !$isSimulate) {
            CustomerEmail::whereIn('id', $customers->pluck('id')->all())
                ->update([
                    'status_blast' => 'ready',
                    'updated_who'  => session('cosmosender_user.user_name', 'Admin'),
                ]);
        }

        $recipients = $customers->map(function ($c) {
            return [
                'id'    => $c->id,
                'email' => $c->email,
                'name'  => $c->nama ?: '-',
            ];
        })->values();

        return response()->json([
            'code'        => 200,
            'rule_type'   => 'blast',
            'rule_name'   => $rule->name,
            'relay_count' => $activeRelaysCount,
            'data'        => $recipients,
        ]);
    }

    /**
     * Eksekusi pengiriman email per item/batch secara aman dari sisi server
     * menggunakan rotasi Round-Robin Relay dan mendukung Mode Simulasi (Sandbox).
     */
    public function sendItem(Request $request, Brand $brand, SmtpBlastService $blastService)
    {
        $validated = $request->validate([
            'content_id'      => 'required|integer',
            'rule_id'         => 'required|integer',
            'recipient_id'    => 'required|integer',
            'recipient_email' => 'required|email',
            'relay_index'     => 'required|integer|min:0',
            'is_last'         => 'nullable|boolean',
            'simulate'        => 'nullable|boolean',
        ]);

        $content = Content::where('brand_id', $brand->id)->findOrFail($validated['content_id']);
        $rule = BlastRule::with(['activeDetails.relay.emailHost'])
            ->where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->findOrFail($validated['rule_id']);

        $activeRelays = $rule->activeDetails
            ->map(function ($detail) {
                return $detail->relay;
            })
            ->filter(function ($relay) {
                return $relay && $relay->flag === 'Y';
            })
            ->values();

        if ($activeRelays->isEmpty()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Tidak ada SMTP Relay aktif pada rule ini.',
            ], 422);
        }

        $relayPos = ((int) $validated['relay_index']) % $activeRelays->count();
        $selectedRelay = $activeRelays->get($relayPos);

        $isBlastRule = $rule->type === 'blast';
        $isStagingAccount = session('cosmosender_user.mode') === 'staging';

        // Pengaman: Akun dev (Staging) otomatis menjalankan Mode Simulasi jika rule bertipe 'blast' (pelanggan asli).
        // Namun untuk rule bertipe 'test' (Send Test), akun dev tetap bisa mengirim email sungguhan ke email tester kecuali jika saklar simulasi diaktifkan manual.
        $simulateOnly = (bool) $request->input('simulate', false);
        if ($isStagingAccount && $isBlastRule) {
            $simulateOnly = true;
        }

        $subject = $isBlastRule
            ? (string) $content->subject
            : '[TEST] ' . (string) $content->subject;

        $result = $blastService->sendSingleEmail(
            $brand,
            $content,
            $selectedRelay,
            $validated['recipient_email'],
            $subject,
            $simulateOnly
        );

        // Jika pengiriman blast sungguhan, perbarui pointer last_email_id, status_blast, dan catat t_blast_log
        if ($isBlastRule && !$simulateOnly) {
            $brand->update([
                'last_email_id'   => $validated['recipient_id'],
                'last_blast_time' => now(),
            ]);

            CustomerEmail::where('id', $validated['recipient_id'])->update([
                'status_blast' => $result['success'] ? 'sent' : 'failed',
                'updated_who'  => session('cosmosender_user.user_name', 'Admin'),
            ]);

            if ($request->boolean('is_last')) {
                BlastLog::create([
                    'brand_id'      => $brand->id,
                    'content_id'    => $content->id,
                    'last_email_id' => $validated['recipient_id'],
                    'status'        => $result['success'] ? 'completed' : 'partial',
                    'flag'          => 'Y',
                ]);
            }
        } elseif ($request->boolean('is_last')) {
            // Catat juga riwayat test / simulasi di t_blast_log agar mudah diaudit
            BlastLog::create([
                'brand_id'      => $brand->id,
                'content_id'    => $content->id,
                'last_email_id' => $validated['recipient_id'],
                'status'        => $simulateOnly ? 'simulated' : 'test_sent',
                'flag'          => 'Y',
            ]);
        }

        $completedCycle = ($relayPos === $activeRelays->count() - 1);

        return response()->json([
            'code'            => 200,
            'result'          => $result,
            'completed_cycle' => $completedCycle,
            'delay_seconds'   => $completedCycle && !$simulateOnly ? 5 : ($completedCycle ? 1 : 0),
        ]);
    }
}

