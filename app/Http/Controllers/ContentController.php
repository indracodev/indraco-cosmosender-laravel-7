<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Content;
use App\Services\ContentSyncService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Brand $brand)
    {
        $brand->load('service.server');

        $contents = Content::where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->withCount(['assets', 'completedBlastLogs'])
            ->orderBy('date_namespace', 'desc')
            ->orderBy('time_namespace', 'desc')
            ->paginate(50);

        $sourceContents = Content::where('brand_id', $brand->id)
            ->where('flag', 'Y')
            ->select('id', 'materi_name', 'subject', 'date_namespace', 'time_namespace')
            ->orderBy('date_namespace', 'desc')
            ->orderBy('time_namespace', 'desc')
            ->limit(100)
            ->get();

        return view('content.index', compact('brand', 'contents', 'sourceContents'));
    }

    public function store(Request $request, Brand $brand, ContentSyncService $syncService)
    {
        $validated = $request->validate([
            'materi_name'       => 'required|string|max:255',
            'subject'           => 'required|string|max:255',
            'blast_date'        => 'required|date',
            'blast_time'        => 'required|string|max:10',
            'source_content_id' => 'nullable|integer',
        ]);

        // Konversi YYYY-MM-DD menjadi YYMMDD dan HH:MM menjadi HHMM
        $dateParts = explode('-', $validated['blast_date']);
        $dateNamespace = substr($dateParts[0], 2, 2) . $dateParts[1] . $dateParts[2];
        $timeNamespace = str_replace(':', '', $validated['blast_time']);

        $exists = Content::where('brand_id', $brand->id)
            ->where('date_namespace', $dateNamespace)
            ->where('time_namespace', $timeNamespace)
            ->exists();

        if ($exists) {
            return response()->json([
                'code'    => 422,
                'message' => 'Materi pada kombinasi Tanggal dan Jam tersebut sudah terdaftar.',
            ], 422);
        }

        $initialBodyContent = '';
        if (!empty($validated['source_content_id'])) {
            $sourceContent = Content::where('brand_id', $brand->id)
                ->where('flag', 'Y')
                ->find($validated['source_content_id']);

            if ($sourceContent) {
                $initialBodyContent = (string) $sourceContent->body_content;
            }
        }

        $content = Content::create([
            'brand_id'       => $brand->id,
            'materi_name'    => $validated['materi_name'],
            'subject'        => $validated['subject'],
            'date_namespace' => $dateNamespace,
            'time_namespace' => $timeNamespace,
            'body_content'   => $initialBodyContent,
            'flag'           => 'Y',
        ]);

        $isLiveAccount = session('cosmosender_user.mode') === 'live';
        $brand->load('service.server');
        $syncService->saveContentFiles($brand, $content, $isLiveAccount);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menambahkan materi "' . $content->materi_name . '".',
            'data'    => $content,
        ]);
    }

    public function updateMeta(Request $request, Content $content)
    {
        $content->load('brand');
        if ($content->isBlasted($content->brand)) {
            return response()->json([
                'code'    => 422,
                'message' => 'Materi ini sudah pernah di-blasting ke pelanggan dan informasinya tidak dapat diubah.',
            ], 422);
        }

        $validated = $request->validate([
            'materi_name' => 'required|string|max:255',
            'subject'     => 'required|string|max:255',
        ]);

        $content->update([
            'materi_name' => $validated['materi_name'],
            'subject'     => $validated['subject'],
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui informasi materi "' . $content->materi_name . '".',
        ]);
    }

    public function editorData(Content $content, ContentSyncService $syncService)
    {
        $content->load(['brand.service.server', 'assets']);
        $brand = $content->brand;
        $isBlasted = $content->isBlasted($brand);

        $assets = $syncService->formatAssetUrls($brand, $content);
        $redirects = $this->buildRedirectList($brand, (string) $content->date_namespace);

        return view('content.editor', compact('brand', 'content', 'assets', 'redirects', 'isBlasted'));
    }

    public function saveBody(Request $request, Content $content, ContentSyncService $syncService)
    {
        $content->load('brand.service.server');
        if ($content->isBlasted($content->brand)) {
            return response()->json([
                'code'    => 422,
                'message' => 'Materi ini sudah pernah di-blasting ke pelanggan dan kode HTML-nya telah dikunci.',
            ], 422);
        }

        $request->validate([
            'body_content' => 'nullable|string',
        ]);

        $content->update([
            'body_content' => (string) $request->input('body_content', ''),
        ]);

        $isLiveAccount = session('cosmosender_user.mode') === 'live';
        $syncResult = $syncService->saveContentFiles($content->brand, $content, $isLiveAccount);

        return response()->json([
            'code'    => 200,
            'message' => 'Konten HTML berhasil disimpan. ' . $syncResult['message'],
            'data'    => $syncResult,
        ]);
    }

    public function uploadAsset(Request $request, Content $content, ContentSyncService $syncService)
    {
        $content->load('brand.service.server');
        if ($content->isBlasted($content->brand)) {
            return response()->json([
                'code'    => 422,
                'message' => 'Materi ini sudah pernah di-blasting ke pelanggan dan penambahan asset telah dikunci.',
            ], 422);
        }

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ], [
            'files.required' => 'Pilih minimal 1 file gambar.',
            'files.*.mimes'  => 'Format gambar harus berupa JPG, PNG, GIF, atau WEBP.',
            'files.*.max'    => 'Ukuran gambar maksimal 5MB per file.',
        ]);

        $isLiveAccount = session('cosmosender_user.mode') === 'live';

        foreach ($request->file('files') as $file) {
            $syncService->storeUploadedAsset($content->brand, $content, $file, $isLiveAccount);
        }

        $content->load('assets');
        $assets = $syncService->formatAssetUrls($content->brand, $content);

        return response()->json([
            'code'    => 200,
            'message' => 'Asset gambar berhasil diunggah.',
            'assets'  => $assets,
        ]);
    }

    public function pushToLive(Content $content, ContentSyncService $syncService)
    {
        $content->load(['brand.service.server', 'assets']);
        if ($content->isBlasted($content->brand)) {
            return response()->json([
                'code'    => 422,
                'message' => 'Materi ini sudah pernah di-blasting ke pelanggan dan telah dikunci.',
            ], 422);
        }

        $result = $syncService->pushAllToLive($content->brand, $content);

        return response()->json([
            'code'    => 200,
            'message' => $result['message'],
            'data'    => $result,
        ]);
    }

    public function preview(Content $content)
    {
        $html = (string) $content->body_content;
        if (trim($html) === '') {
            $html = '<div style="font-family:sans-serif;padding:2rem;color:#64748b;text-align:center;">Belum ada kode HTML pada materi ini.</div>';
        } else {
            $html = str_replace('{email}', 'preview@supresso.com', $html);
        }

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function buildRedirectList(Brand $brand, string $edmDate): array
    {
        $list = [
            [
                'name' => 'Tracking Pixel (Open / Read Email)',
                'url'  => 'https://www.cosmosender.supresso.co.id/edmsavelog/index.php?to=read&edm=' . $edmDate . '&act=open&email={email}',
            ],
        ];

        if ((int) $brand->id === 10 || strtolower((string) $brand->slug) === 'supresso-sg') {
            $sgUrls = [
                ['name' => 'SG - Open / Read Email', 'url' => "https://supresso.com/sg/public/redirect-edm/read/{$edmDate}/open/{email}"],
                ['name' => 'SG - Privacy Policy', 'url' => "https://supresso.com/sg/public/redirect-edm/policy/{$edmDate}/click/{email}"],
                ['name' => 'SG - Unsubscribe', 'url' => "https://supresso.com/sg/public/redirect-edm/unsubscribe/{$edmDate}/click/{email}"],
                ['name' => 'SG - Instagram', 'url' => "https://supresso.com/sg/public/redirect-edm/instagram/{$edmDate}/click/{email}"],
                ['name' => 'SG - Facebook', 'url' => "https://supresso.com/sg/public/redirect-edm/fb/{$edmDate}/click/{email}"],
                ['name' => 'SG - Youtube', 'url' => "https://supresso.com/sg/public/redirect-edm/youtube/{$edmDate}/click/{email}"],
                ['name' => 'SG - Tiktok', 'url' => "https://supresso.com/sg/public/redirect-edm/tiktok/{$edmDate}/click/{email}"],
                ['name' => 'SG - Maps', 'url' => "https://supresso.com/sg/public/redirect-edm/maps/{$edmDate}/click/{email}"],
                ['name' => 'SG - Best Seller', 'url' => "https://supresso.com/sg/public/redirect-edm/web-best-seller/{$edmDate}/click/{email}"],
                ['name' => 'SG - Clearance Sale', 'url' => "https://supresso.com/sg/public/redirect-edm/web-clearance-sale/{$edmDate}/click/{email}"],
                ['name' => 'SG - Tab Collection', 'url' => "https://supresso.com/sg/public/redirect-edm/{collection-id}/{$edmDate}/click/{email}"],
            ];
            $list = array_merge($list, $sgUrls);
        }

        return $list;
    }
}
