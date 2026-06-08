<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [
        'base_fee',
        'fee_per_km',
        'free_distance',
        'max_radius',
        'store_latitude',
        'store_longitude',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'integer',
            'fee_per_km' => 'integer',
            'free_distance' => 'decimal:2',
            'max_radius' => 'decimal:2',
            'store_latitude' => 'decimal:7',
            'store_longitude' => 'decimal:7',
        ];
    }

    public static function active(): self
    {
        return self::query()->firstOrCreate([], [
            'base_fee' => 3000,
            'fee_per_km' => 2000,
            'max_radius' => 20,
        ]);
    }
}
