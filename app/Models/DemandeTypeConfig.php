<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DemandeTypeConfig extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'demande_type_configs';

    protected $fillable = [
        'type',
        'maxAmount',
    ];

    protected $casts = [
        'maxAmount' => 'float',
    ];

    public static function maxAmountFor(string $type, ?float $fallback = null): ?float
    {
        $value = static::query()->where('type', $type)->value('maxAmount');
        if ($value !== null && $value !== '') {
            return (float) $value;
        }

        if ($fallback === null) {
            return null;
        }

        $record = static::query()->firstOrCreate(
            ['type' => $type],
            ['maxAmount' => $fallback]
        );

        return (float) ($record->maxAmount ?? $fallback);
    }
}
