<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pret extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'prets';

    protected $fillable = [
        'demandeId',            // Reference to Demande
        'employeeId',           // Reference to User
        'interestRate',         // Float (%)
        'startDate',            // DateTime
        'endDate',              // DateTime
        'durationMonths',       // Total months
        'amount',               // Float - total loan amount
        'status',               // active, completed, defaulted
        'remainingBalance',     // Float
        'totalPaid',            // Float
        'paidAt',               // DateTime when fully paid
    ];

    protected $casts = [
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        'paidAt' => 'datetime',
        'amount' => 'float',
        'interestRate' => 'float',
        'remainingBalance' => 'float',
        'totalPaid' => 'float',
    ];

    // Relationships
    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demandeId');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }

    public function mensualites()
    {
        return $this->hasMany(Mensualite::class, 'pretId');
    }
}
