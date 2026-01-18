<?php

namespace App\Models;

class AideSociale extends Demande
{
    protected $collection = 'aides_sociales';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->fillable = array_merge(static::getParentFillable(), [
            'reasonCode',
            'category',
            'familySize',
            'monthlyIncome',
        ]);

        $this->casts = array_merge(static::getParentCasts(), [
            'monthlyIncome' => 'float',
            'familySize' => 'integer',
        ]);
    }
}
