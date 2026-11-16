<?php

namespace App\Models;

class EmailRelay extends BaseCosmosenderModel
{
    protected $table = 'm_email_relay';

    protected $fillable = [
        'host_id',
        'email',
        'email_alias',
        'email_from',
        'password',
        'port',
        'flag',
        'created_who',
        'updated_who',
    ];

    protected $hidden = [
        'password',
    ];

    public function emailHost()
    {
        return $this->belongsTo(EmailHost::class, 'host_id');
    }
}
