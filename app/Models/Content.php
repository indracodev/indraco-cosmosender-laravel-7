<?php

namespace App\Models;

use Carbon\Carbon;

class Content extends BaseCosmosenderModel
{
    protected $table = 't_content';

    protected $fillable = [
        'brand_id',
        'body_content',
        'date_namespace',
        'time_namespace',
        'materi_name',
        'subject',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'content_id')->where('flag', 'Y')->orderBy('created_at', 'desc');
    }

    public function completedBlastLogs()
    {
        return $this->hasMany(BlastLog::class, 'content_id')
            ->where('flag', 'Y')
            ->whereIn('status', ['completed', 'partial']);
    }

    /**
     * Mengecek apakah materi konten ini sudah pernah di-blasting ke pelanggan.
     */
    public function isBlasted(?Brand $brand = null): bool
    {
        if (isset($this->attributes['completed_blast_logs_count'])) {
            if ((int) $this->attributes['completed_blast_logs_count'] > 0) {
                return true;
            }
        } elseif ($this->completedBlastLogs()->exists()) {
            return true;
        }

        $targetBrand = $brand ?: ($this->relationLoaded('brand') ? $this->brand : null);
        if ($targetBrand && !empty($targetBrand->last_blast_time) && $targetBrand->last_blast_time !== '0000-00-00 00:00:00') {
            try {
                $lastBlastDateTimeNs = Carbon::parse($targetBrand->last_blast_time)->format('ymdHi');
                $contentDateNs = (string) $this->date_namespace;
                $contentTimeNs = str_pad((string) $this->time_namespace, 4, '0', STR_PAD_LEFT);
                if (strlen($contentDateNs) === 6 && ($contentDateNs . $contentTimeNs) <= $lastBlastDateTimeNs) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Abaikan jika format tanggal tidak valid
            }
        }

        return false;
    }

    public function getFormattedDateAttribute(): string
    {
        $str = (string) $this->date_namespace;
        if (strlen($str) === 6) {
            return '20' . substr($str, 0, 2) . '-' . substr($str, 2, 2) . '-' . substr($str, 4, 2);
        }
        return $str ?: '-';
    }

    public function getFormattedTimeAttribute(): string
    {
        $str = (string) $this->time_namespace;
        if (strlen($str) === 4) {
            return substr($str, 0, 2) . ':' . substr($str, 2, 2);
        }
        return $str ?: '-';
    }
}
