<?php

namespace App\Models;

class DonsScolaire extends Demande
{
    protected $collection = 'dons_scolaires';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->fillable = array_merge(static::getParentFillable(), [
            'studentName',
            'schoolName',
            'schoolLevel',
            'academicYear',
            'tuitionAmount',
            'purpose',
        ]);

        $this->casts = array_merge(static::getParentCasts(), [
            'tuitionAmount' => 'float',
        ]);
    }
}
