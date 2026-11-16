<?php

namespace App\Models;

class EmailHost extends BaseCosmosenderModel
{
    protected $table = 'm_email_host';

    protected $fillable = [
        'host',
        'server_id',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function relays()
    {
        return $this->hasMany(EmailRelay::class, 'host_id');
    }
}
