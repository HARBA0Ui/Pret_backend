<?php

namespace App\Models;

class PretExceptionnel extends Demande
{
    protected $collection = 'prets_exceptionnels';

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
