<?php

namespace App\Models;

class PretHajj extends Demande
{
    protected $collection = 'prets_hajj';

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
