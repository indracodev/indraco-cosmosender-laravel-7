<?php

namespace App\Models;

class Asset extends BaseCosmosenderModel
{
    protected $table = 't_asset';

    protected $fillable = [
        'content_id',
        'name',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}

