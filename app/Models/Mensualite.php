<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Mensualite extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mensualites';

    protected $fillable = [
        'pretId',
        'employeeId',
        'paymentNumber',
        'amount',
        'dueDate',
        'paidAmount',
        'paidDate',
        'status',
        'createdAt',
        'updatedAt',
    ];

    protected $casts = [
        'paymentNumber' => 'integer',
        'amount' => 'float',
        'dueDate' => 'datetime',
        'paidAmount' => 'float',
        'paidDate' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function pret()
    {
        return $this->belongsTo(Pret::class, 'pretId');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }
}
