<?php

namespace App\Models;

class CustomerEmail extends BaseCosmosenderModel
{
    protected $table = 'mt_customer_email';

    protected $fillable = [
        'brand_id',
        'nama',
        'email',
        'no_batch',
        'status_blast',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}

