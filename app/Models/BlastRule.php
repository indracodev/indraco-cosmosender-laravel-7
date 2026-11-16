<?php

namespace App\Models;

class BlastRule extends BaseCosmosenderModel
{
    protected $table = 'm_blast_rule';

    protected $fillable = [
        'name',
        'brand_id',
        'host_id',
        'type',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function emailHost()
    {
        return $this->belongsTo(EmailHost::class, 'host_id');
    }

    public function activeDetails()
    {
        return $this->hasMany(BlastRuleDetail::class, 'rule_id')->where('flag', 'Y');
    }

    public function activeRecipients()
    {
        return $this->hasMany(BlastRuleRecipient::class, 'rule_id')->where('flag', 'Y');
    }
}
