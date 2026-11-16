<?php

namespace App\Models;

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

