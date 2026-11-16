<?php

namespace App\Models;

class UserAccount extends BaseCosmosenderModel
{
    protected $table = 'm_user';

    protected $fillable = [
        'user_name',
        'real_name',
        'password',
        'flag',
        'created_who',
        'updated_who',
    ];

    protected $hidden = [
        'password',
    ];

    public function isStagingAccount(): bool
    {
        return strtolower((string) $this->user_name) === 'dev';
    }
}

