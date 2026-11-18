<?php

namespace App\Models;

use Carbon\Carbon;

class Brand extends BaseCosmosenderModel
{
    protected $table = 'm_brand';

    protected $fillable = [
        'name',
        'email_alias',
        'slug',
        'domain',
        'actual_path',
        'content_domain',
        'aseet_namespace',
        'service_id',
        'email_last_sync',
        'flag_blast',
        'flag_email_sync',
        'blast_limit',
        'blast_hour_interval',
        'last_time_sequence',
        'last_email_id',
        'last_blast_time',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function activeBlastRules()
    {
        return $this->hasMany(BlastRule::class, 'brand_id')->where('flag', 'Y')->orderBy('type', 'desc');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'brand_id');
    }

    public function activeCustomerEmails()
    {
        return $this->hasMany(CustomerEmail::class, 'brand_id')->where('flag', 'Y');
    }

    public function emailSyncTable()
    {
        return $this->hasOne(EmailSyncTable::class, 'brand_id');
    }

    /**
     * Perbaikan bug interval: blast_hour_interval menggunakan satuan Jam,
     * sehingga dikalikan 60 menit saat dibandingkan dengan selisih menit dari last_blast_time.
     */
    public function isReadyToBlast(): bool
    {
        $intervalHours = (int) $this->blast_hour_interval;
        if ($intervalHours <= 0 || empty($this->last_blast_time) || $this->last_blast_time === '0000-00-00 00:00:00') {
            return true;
        }

        try {
            $lastBlast = Carbon::parse($this->last_blast_time);
            $requiredMinutes = $intervalHours * 60;
            return $lastBlast->diffInMinutes(Carbon::now(), false) >= $requiredMinutes;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function remainingWaitMinutes(): int
    {
        $intervalHours = (int) $this->blast_hour_interval;
        if ($intervalHours <= 0 || empty($this->last_blast_time) || $this->last_blast_time === '0000-00-00 00:00:00') {
            return 0;
        }

        try {
            $lastBlast = Carbon::parse($this->last_blast_time);
            $requiredMinutes = $intervalHours * 60;
            $elapsed = $lastBlast->diffInMinutes(Carbon::now(), false);
            return max(0, $requiredMinutes - $elapsed);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getServiceUrlAttribute(): string
    {
        $protocol = env('COSMOSENDER_REMOTE_PROTOCOL', 'https://');
        if ($this->service && $this->service->server) {
            return rtrim($protocol . $this->service->server->domain . $this->service->service, '/') . '/';
        }
        return '';
    }

    public function getUploadPathAttribute(): string
    {
        $parts = array_filter([
            rtrim((string) $this->actual_path, '/'),
            trim((string) $this->domain, '/'),
            trim((string) $this->aseet_namespace, '/'),
        ], function ($part) {
            return $part !== '';
        });

        return implode('/', $parts) . '/';
    }
}
