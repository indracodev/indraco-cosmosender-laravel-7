<?php

namespace App\Models;

class BlastRuleRecipient extends BaseCosmosenderModel
{
    protected $table = 'm_blast_rule_recipient';

    protected $fillable = [
        'email',
        'name',
        'rule_id',
        'flag',
        'created_who',
        'updated_who',
    ];

    public function rule()
    {
        return $this->belongsTo(BlastRule::class, 'rule_id');
    }
}

