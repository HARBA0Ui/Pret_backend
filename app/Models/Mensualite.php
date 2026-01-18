<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Mensualite extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mensualites';

    protected $fillable = [
        'pretId',               // Reference to Pret
        'employeeId',           // Reference to User
        'paymentNumber',        // 1, 2, 3, ...
        'dueDate',              // DateTime
        'amount',               // Float - monthly payment amount
        'status',               // pending, paid, overdue
        'paidDate',             // DateTime (null if not paid)
        'paidAmount',           // Float (actual paid amount)
        'notes',
    ];

    protected $casts = [
        'dueDate' => 'datetime',
        'paidDate' => 'datetime',
        'amount' => 'float',
        'paidAmount' => 'float',
    ];

    // Relationships
    public function pret()
    {
        return $this->belongsTo(Pret::class, 'pretId');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }
}
