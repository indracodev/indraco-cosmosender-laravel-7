<?php

namespace App\Models;

class BlastRuleDetail extends BaseCosmosenderModel
{
    protected $table = 'm_blast_rule_detail';

    protected $fillable = [
        'relay_id',
        'rule_id',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function rule()
    {
        return $this->belongsTo(BlastRule::class, 'rule_id');
    }

    public function relay()
    {
        return $this->belongsTo(EmailRelay::class, 'relay_id');
    }
}

