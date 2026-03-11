<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pret extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'prets';

    protected $fillable = [
        'demandeId',
        'employeeId',
        'amount',
        'interestRate',
        'dureeMonths',
        'startDate',
        'totalToRepay',
        'remainingBalance',
        'status',
        'createdAt',
        'updatedAt',
    ];

    protected $casts = [
        'amount' => 'float',
        'interestRate' => 'float',
        'dureeMonths' => 'integer',
        'startDate' => 'datetime',
        'totalToRepay' => 'float',
        'remainingBalance' => 'float',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }

    public function mensualites()
    {
        return $this->hasMany(Mensualite::class, 'pretId');
    }
}
