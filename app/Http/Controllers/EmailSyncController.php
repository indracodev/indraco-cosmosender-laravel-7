<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\CustomerEmail;
use App\Models\EmailSyncTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmailSyncController extends Controller
{
    public function index()
    {
        $brands = Brand::with(['service.server', 'emailSyncTable'])
            ->withCount('activeCustomerEmails as subscriber_count')
            ->orderBy('id', 'asc')
            ->paginate(50);

        return view('emailsync.index', compact('brands'));
    }

    public function customers(Request $request, Brand $brand)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('flag', 'Y');

        $query = CustomerEmail::where('brand_id', $brand->id);

        if (in_array($status, ['Y', 'N'], true)) {
            $query->where('flag', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $paginated = $query->orderBy('id', 'asc')->paginate(50);

        return response()->json([
            'code' => 200,
            'data' => $paginated,
        ]);
    }

    public function saveConfig(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'table_name'   => 'required|string|max:100',
            'email_column' => 'required|string|max:100',
            'name_column'  => 'nullable|string|max:100',
            'where_clause' => 'nullable|string|max:100',
            'where_value'  => 'nullable|string|max:100',
        ]);

        EmailSyncTable::updateOrCreate(
            ['brand_id' => $brand->id],
            [
                'table_name'   => $validated['table_name'],
                'email_column' => $validated['email_column'],
                'name_column'  => (string) ($validated['name_column'] ?? ''),
                'where_clause' => (string) ($validated['where_clause'] ?? ''),
                'where_value'  => (string) ($validated['where_value'] ?? ''),
                'flag'         => 'Y',
            ]
        );

        return response()->json([
            'code'    => 200,
            'message' => 'Konfigurasi sinkronisasi untuk ' . $brand->name . ' berhasil disimpan.',
        ]);
    }

    public function syncFromRemote(Request $request, Brand $brand)
    {
        $brand->load(['service.server', 'emailSyncTable']);
        $config = $brand->emailSyncTable;

        if (!$config || empty($config->table_name) || empty($config->email_column)) {
            return response()->json([
                'code'    => 422,
                'message' => 'Lengkapi Konfigurasi Tabel dan Kolom Email terlebih dahulu sebelum melakukan sinkronisasi.',
            ], 422);
        }

        $mode = $request->input('mode', 'regular');
        $serviceUrl = $brand->service_url;

        if (!$serviceUrl) {
            return response()->json([
                'code'    => 422,
                'message' => 'Brand belum memiliki konfigurasi Content Service / Server yang valid.',
            ], 422);
        }

        try {
            $payload = $mode === 'membership'
                ? [
                    'do'          => 'get-email-sync-membership',
                    'slug'        => $brand->slug,
                    'emailcolumn' => $config->email_column,
                    'namecolumn'  => $config->name_column,
                ]
                : [
                    'do'           => 'get-email-sync',
                    'tbname'       => $config->table_name,
                    'email_column' => $config->email_column,
                    'name_column'  => $config->name_column ?: $config->email_column,
                    'where_clause' => $config->where_clause,
                    'where_value'  => $config->where_value,
                    'slug'         => $brand->slug,
                ];

            $response = Http::timeout(30)->asForm()->post($serviceUrl . 'api/api-sync.php', $payload);

            if (!$response->successful()) {
                return response()->json([
                    'code'    => 502,
                    'message' => 'Server sumber tidak dapat dihubungi (HTTP ' . $response->status() . ').',
                ], 502);
            }

            $json = $response->json();
            $rows = $json['data'] ?? [];

            if (empty($rows) || !is_array($rows)) {
                return response()->json([
                    'code'    => 422,
                    'message' => 'Tidak ada data email baru yang ditemukan dari server sumber.',
                ], 422);
            }

            $count = $this->processBatchSync($brand, $rows, $config->email_column, (string) $config->name_column);

            return response()->json([
                'code'    => 200,
                'message' => "Berhasil menyinkronkan {$count} kontak pelanggan untuk {$brand->name}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Gagal menghubungi endpoint sinkronisasi eksternal: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processBatchSync(Brand $brand, array $rows, string $emailCol, string $nameCol): int
    {
        $batchNo = $brand->id . now()->format('ymdHis');
        $actor   = session('cosmosender_user.user_name', 'Admin');
        $syncedEmails = [];

        DB::transaction(function () use ($brand, $rows, $emailCol, $nameCol, $batchNo, $actor, &$syncedEmails) {
            $existingMap = CustomerEmail::where('brand_id', $brand->id)
                ->pluck('id', 'email')
                ->all();

            $newRecords = [];
            $updateIds  = [];

            foreach ($rows as $row) {
                $email = trim((string) ($row[$emailCol] ?? $row['email'] ?? ''));
                if ($email === '') {
                    continue;
                }

                $name = $nameCol !== '' ? str_replace("'", '', (string) ($row[$nameCol] ?? '')) : '';
                $syncedEmails[] = $email;

                if (isset($existingMap[$email])) {
                    $updateIds[] = $existingMap[$email];
                } else {
                    $newRecords[] = [
                        'brand_id'    => $brand->id,
                        'nama'        => $name,
                        'email'       => $email,
                        'no_batch'    => $batchNo,
                        'flag'        => 'Y',
                        'created_at'  => now(),
                        'created_who' => $actor,
                        'updated_at'  => now(),
                        'updated_who' => $actor,
                    ];
                    $existingMap[$email] = true;
                }
            }

            foreach (array_chunk($updateIds, 500) as $chunkIds) {
                CustomerEmail::whereIn('id', $chunkIds)->update([
                    'no_batch'    => $batchNo,
                    'flag'        => 'Y',
                    'updated_at'  => now(),
                    'updated_who' => $actor,
                ]);
            }

            foreach (array_chunk($newRecords, 500) as $chunkNew) {
                CustomerEmail::insert($chunkNew);
            }

            // Nonaktifkan kontak yang tidak lagi ada di batch terbaru (hanya 1 query bulk update!)
            CustomerEmail::where('brand_id', $brand->id)
                ->where('no_batch', '!=', $batchNo)
                ->update([
                    'flag'        => 'N',
                    'updated_at'  => now(),
                    'updated_who' => $actor,
                ]);

            $brand->update([
                'email_last_sync' => now(),
            ]);
        });

        return count($syncedEmails);
    }
}

