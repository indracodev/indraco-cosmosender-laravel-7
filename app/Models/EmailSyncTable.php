<?php

namespace App\Models;

class EmailSyncTable extends BaseCosmosenderModel
{
    protected $table = 'm_email_sync_table';

    protected $fillable = [
        'brand_id',
        'table_name',
        'email_column',
        'name_column',
        'where_clause',
        'where_value',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}

