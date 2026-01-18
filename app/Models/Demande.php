<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

abstract class Demande extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'status',
        'employeeId',
        'amountRequested',
        'approvedAmount',
        'submittedAt',
        'reviewedAt',
        'decisionAt',
        'reviewByAdminId',
        'reason',
        'attachments',
    ];

    protected $casts = [
        'submittedAt' => 'datetime',
        'reviewedAt' => 'datetime',
        'decisionAt' => 'datetime',
        'amountRequested' => 'float',
        'approvedAmount' => 'float',
        'attachments' => 'array',
    ];

    // Helper methods for child classes
    protected static function getParentFillable(): array
    {
        return [
            'status',
            'employeeId',
            'amountRequested',
            'approvedAmount',
            'submittedAt',
            'reviewedAt',
            'decisionAt',
            'reviewByAdminId',
            'reason',
            'attachments',
        ];
    }

    protected static function getParentCasts(): array
    {
        return [
            'submittedAt' => 'datetime',
            'reviewedAt' => 'datetime',
            'decisionAt' => 'datetime',
            'amountRequested' => 'float',
            'approvedAmount' => 'float',
            'attachments' => 'array',
        ];
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(User::class, 'employeeId');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewByAdminId');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'demandeId');
    }

    public function prets()
    {
        return $this->hasMany(Pret::class, 'demandeId');
    }
}
