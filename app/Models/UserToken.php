<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 't_token_user';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'login_at',
        'logout_at',
    ];
}

