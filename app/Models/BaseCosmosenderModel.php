<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseCosmosenderModel extends Model
{
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $actor = session('cosmosender_user.user_name', 'Admin');
            if (empty($model->created_who)) {
                $model->created_who = $actor;
            }
            if (empty($model->flag)) {
                $model->flag = 'Y';
            }
        });

        static::updating(function ($model) {
            $actor = session('cosmosender_user.user_name', 'Admin');
            $model->updated_who = $actor;
        });
    }

    public function scopeActive($query)
    {
        return $query->where($this->getTable() . '.flag', 'Y');
    }
}

