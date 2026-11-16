<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Content;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ContentSyncService
{
    /**
     * Dapatkan direktori lokal penyimpanan konten & asset untuk pengujian lokal/staging.
     */
    public function getLocalRelativeDir(Brand $brand, Content $content): string
    {
        $domain = trim((string) $brand->domain, '/');
        $ns     = trim((string) $brand->aseet_namespace, '/');
        $date   = trim((string) $content->date_namespace, '/');
        $time   = trim((string) $content->time_namespace, '/');

        return "uploads/{$domain}/{$ns}/{$date}/{$time}";
    }

    /**
     * Simpan file index.html ke direktori lokal, dan jika akun Live (atau diminta push),
     * kirim juga ke mbcontentservices server live.
     */
    public function saveContentFiles(Brand $brand, Content $content, bool $pushToLive = false): array
    {
        $relDir  = $this->getLocalRelativeDir($brand, $content);
        $fullDir = public_path($relDir);

        if (!File::exists($fullDir . '/img')) {
            File::makeDirectory($fullDir . '/img', 0775, true);
        }

        File::put($fullDir . '/index.html', (string) $content->body_content);

        $remoteSynced = false;
        $remoteMessage = 'Tersimpan di penyimpanan lokal.';

        if ($pushToLive && $brand->service_url) {
            try {
                $remotePath = $brand->upload_path . $content->date_namespace . '/' . $content->time_namespace . '/';

                // Pastikan direktori di server live tersedia
                Http::timeout(10)->asForm()->post($brand->service_url . 'api/api-content.php', [
                    'do'    => 'create-content',
                    'token' => '1',
                    'id'    => '1',
                    'brand' => json_encode($brand->toArray()),
                    'date'  => $content->date_namespace,
                    'time'  => $content->time_namespace,
                ]);

                // Upload isi HTML ke server live
                $response = Http::timeout(15)->asForm()->post($brand->service_url . 'api/api-content.php', [
                    'do'      => 'upload-content',
                    'token'   => '1',
                    'id'      => '1',
                    'content' => (string) $content->body_content,
                    'path'    => $remotePath,
                ]);

                if ($response->successful()) {
                    $remoteSynced = true;
                    $remoteMessage = 'Berhasil disimpan ke lokal & disinkronkan ke Server Live.';
                } else {
                    $remoteMessage = 'Tersimpan di lokal (Server Live merespons HTTP ' . $response->status() . ').';
                }
            } catch (\Throwable $e) {
                $remoteMessage = 'Tersimpan di lokal (Server Live tidak terjangkau dari jaringan ini).';
            }
        }

        return [
            'local_url'     => url($relDir . '/index.html'),
            'remote_synced' => $remoteSynced,
            'message'       => $remoteMessage,
        ];
    }

    /**
     * Simpan file gambar asset ke lokal dan (jika mode Live / Push) kirim ke mbcontentservices.
     */
    public function storeUploadedAsset(Brand $brand, Content $content, $uploadedFile, bool $pushToLive = false): Asset
    {
        $ext = strtolower($uploadedFile->getClientOriginalExtension());
        // Kompatibel PHP 7.2 (menggantikan hrtime(true) yang hanya ada di PHP 7.3+)
        $uniqueStamp = str_replace('.', '', sprintf('%.6f', microtime(true)));
        $safeOriginal = preg_replace('/[^a-zA-Z0-9_\-\.]/', '-', pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = $content->id . '_' . $uniqueStamp . '_' . substr($safeOriginal, 0, 30) . '.' . $ext;

        $relDir = $this->getLocalRelativeDir($brand, $content) . '/img';
        $fullDir = public_path($relDir);

        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0775, true);
        }

        $uploadedFile->move($fullDir, $filename);

        $asset = Asset::create([
            'content_id' => $content->id,
            'name'       => $filename,
            'flag'       => 'Y',
        ]);

        if ($pushToLive) {
            $this->pushSingleAssetToRemote($brand, $content, $fullDir . '/' . $filename, $filename, $ext);
        }

        return $asset;
    }

    /**
     * Push satu file gambar dari lokal ke mbcontentservices server live.
     */
    public function pushSingleAssetToRemote(Brand $brand, Content $content, string $localFilePath, string $filename, string $ext = ''): bool
    {
        if (!$brand->service_url || !File::exists($localFilePath)) {
            return false;
        }

        if ($ext === '') {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        }

        try {
            $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
            $base64 = 'data:' . $mime . ';base64,' . base64_encode(File::get($localFilePath));
            $remotePath = $brand->upload_path . $content->date_namespace . '/' . $content->time_namespace . '/img/';

            $response = Http::timeout(20)->asForm()->post($brand->service_url . 'api/api-content.php', [
                'do'       => 'upload-asset',
                'token'    => '1',
                'id'       => '1',
                'strimg'   => $base64,
                'type'     => $ext,
                'path'     => $remotePath,
                'filename' => $filename,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Push seluruh materi (HTML + semua gambar lokal) dari akun dev/lokal ke Server Live.
     */
    public function pushAllToLive(Brand $brand, Content $content): array
    {
        $htmlResult = $this->saveContentFiles($brand, $content, true);
        $relImgDir  = $this->getLocalRelativeDir($brand, $content) . '/img';
        $pushedAssets = 0;

        foreach ($content->assets as $asset) {
            $localFile = public_path($relImgDir . '/' . $asset->name);
            if (File::exists($localFile)) {
                if ($this->pushSingleAssetToRemote($brand, $content, $localFile, $asset->name)) {
                    $pushedAssets++;
                }
            }
        }

        return [
            'remote_synced' => $htmlResult['remote_synced'],
            'pushed_assets' => $pushedAssets,
            'message'       => $htmlResult['message'] . " ({$pushedAssets} file asset diproses).",
        ];
    }

    /**
     * Menghasilkan URL gambar baik versi Lokal (jika file ada di lokal) maupun URL Server Live.
     */
    public function formatAssetUrls(Brand $brand, Content $content): array
    {
        $protocol  = env('COSMOSENDER_REMOTE_PROTOCOL', 'https://');
        $relImgDir = $this->getLocalRelativeDir($brand, $content) . '/img';
        $result    = [];

        foreach ($content->assets as $asset) {
            $remoteRelative = rtrim((string) $brand->content_domain, '/')
                . trim((string) $brand->domain, '/') . '/'
                . trim((string) $brand->aseet_namespace, '/') . '/'
                . $content->date_namespace . '/'
                . $content->time_namespace . '/img/'
                . $asset->name;

            $liveUrl  = $protocol . ltrim($remoteRelative, '/');
            $localFile = public_path($relImgDir . '/' . $asset->name);
            $hasLocal  = File::exists($localFile);
            $localUrl  = $hasLocal ? url($relImgDir . '/' . $asset->name) : $liveUrl;

            $result[] = [
                'id'        => $asset->id,
                'name'      => $asset->name,
                'local_url' => $localUrl,
                'live_url'  => $liveUrl,
                'has_local' => $hasLocal,
            ];
        }

        return $result;
    }
}

