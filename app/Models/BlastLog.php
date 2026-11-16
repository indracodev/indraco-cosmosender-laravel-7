<?php

namespace App\Models;

class BlastLog extends BaseCosmosenderModel
{
    protected $table = 't_blast_log';

    protected $fillable = [
        'brand_id',
        'content_id',
        'last_email_id',
        'status',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}

