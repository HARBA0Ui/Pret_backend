<?php

namespace App\Models;

class SalaireCollectives extends Demande
{
    protected $collection = 'salaire_collectives';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->fillable = array_merge(static::getParentFillable(), [
            'collectiveAgreement',
            'beneficiaryType',
            'disbursementSchedule',
        ]);

        $this->casts = array_merge(static::getParentCasts(), []);
    }
}
