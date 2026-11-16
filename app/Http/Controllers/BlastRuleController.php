<?php

namespace App\Http\Controllers;

use App\Models\BlastRule;
use App\Models\BlastRuleDetail;
use App\Models\BlastRuleRecipient;
use App\Models\Brand;
use App\Models\EmailHost;
use App\Models\EmailRelay;
use Illuminate\Http\Request;

class BlastRuleController extends Controller
{
    public function index()
    {
        $brands = Brand::with([
            'activeBlastRules.emailHost.server',
            'activeBlastRules.activeDetails.relay.emailHost',
            'activeBlastRules.activeRecipients',
        ])->orderBy('id', 'asc')->get();

        $hosts = EmailHost::with(['server', 'relays' => function ($q) {
            $q->where('flag', 'Y');
        }])->where('flag', 'Y')->orderBy('id', 'asc')->get();

        return view('settings.blastrules', compact('brands', 'hosts'));
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|integer',
            'host_id'  => 'required|integer',
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:test,blast',
        ]);

        BlastRule::create([
            'brand_id' => $validated['brand_id'],
            'host_id'  => $validated['host_id'],
            'name'     => $validated['name'],
            'type'     => $validated['type'],
            'flag'     => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menambahkan Blast Rule "' . $validated['name'] . '".',
        ]);
    }

    public function updateRule(Request $request, BlastRule $rule)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'host_id' => 'required|integer',
        ]);

        $newHostId = (int) $validated['host_id'];
        if ((int) $rule->host_id !== $newHostId) {
            $hasOldHostRelays = BlastRuleDetail::where('rule_id', $rule->id)
                ->where('flag', 'Y')
                ->whereHas('relay', function ($q) use ($rule) {
                    $q->where('host_id', $rule->host_id);
                })
                ->exists();

            if ($hasOldHostRelays) {
                return response()->json([
                    'code'    => 422,
                    'message' => 'Terdapat akun SMTP Relay aktif dari Host sebelumnya. Nonaktifkan relay lama terlebih dahulu sebelum mengganti Host.',
                ], 422);
            }
        }

        $rule->update([
            'name'    => $validated['name'],
            'host_id' => $newHostId,
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui Blast Rule "' . $rule->name . '".',
        ]);
    }

    public function storeRelay(Request $request, BlastRule $rule)
    {
        $validated = $request->validate([
            'relay_id' => 'required|integer',
        ]);

        $relay = EmailRelay::where('flag', 'Y')->findOrFail($validated['relay_id']);

        $alreadyAssigned = BlastRuleDetail::where('rule_id', $rule->id)
            ->where('relay_id', $relay->id)
            ->where('flag', 'Y')
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'code'    => 422,
                'message' => 'Akun SMTP Relay tersebut sudah terdaftar pada rule ini.',
            ], 422);
        }

        BlastRuleDetail::create([
            'rule_id'  => $rule->id,
            'relay_id' => $relay->id,
            'flag'     => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menambahkan SMTP Relay "' . $relay->email . '".',
        ]);
    }

    public function removeRelay(BlastRuleDetail $detail)
    {
        $detail->update(['flag' => 'N']);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menonaktifkan SMTP Relay dari rule.',
        ]);
    }

    public function storeRecipient(Request $request, BlastRule $rule)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'required|string|max:255',
        ]);

        BlastRuleRecipient::create([
            'rule_id' => $rule->id,
            'email'   => $validated['email'],
            'name'    => $validated['name'],
            'flag'    => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menambahkan penerima uji coba "' . $validated['email'] . '".',
        ]);
    }

    public function removeRecipient(BlastRuleRecipient $recipient)
    {
        $recipient->update(['flag' => 'N']);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menghapus penerima uji coba.',
        ]);
    }
}

