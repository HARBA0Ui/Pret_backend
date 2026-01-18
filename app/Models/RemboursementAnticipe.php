<?php

namespace App\Models;

class RemboursementAnticipe extends Demande
{
    protected $collection = 'remboursements_anticipes';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->fillable = array_merge(static::getParentFillable(), [
            'loanIdToRepay',
            'earlyRepaymentAmount',
            'penaltyWaived',
        ]);

        $this->casts = array_merge(static::getParentCasts(), [
            'earlyRepaymentAmount' => 'float',
            'penaltyWaived' => 'boolean',
        ]);
    }
}
