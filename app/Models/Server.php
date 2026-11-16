<?php

namespace App\Models;

class Server extends BaseCosmosenderModel
{
    protected $table = 'm_server';

    protected $fillable = [
        'name',
        'domain',
        'color',
        'flag',
        'created_who',
        'updated_who',
    ];
}
