<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceShippingRate extends Model
{
    protected $fillable = [
        'province_name',
        'shipping_rate',
        'is_active',
    ];

    protected $casts = [
        'shipping_rate' => 'integer',
        'is_active' => 'boolean',
    ];
}
