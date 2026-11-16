<?php

namespace App\Models;

class Service extends BaseCosmosenderModel
{
    protected $table = 'm_service';

    protected $fillable = [
        'server_id',
        'service',
        'color',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
