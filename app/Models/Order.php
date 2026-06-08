<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_email',
        'status',
        'payment_method',
        'products',
        'subtotal',
        'shipping_fee',
        'shipping_courier',
        'shipping_type',
        'shipping_distance_km',
        'discount',
        'total',
        'voucher_code',
        'voucher_title',
        'recipient_name',
        'recipient_phone',
        'address_label',
        'address_detail',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_datetime',
    ];

    protected function casts(): array
    {
        return [
            'products' => 'array',
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'shipping_distance_km' => 'decimal:2',
            'shipping_courier' => 'string',
            'shipping_type' => 'string',
            'discount' => 'integer',
            'total' => 'integer',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_datetime' => 'datetime',
        ];
    }

    public function hasDeliveryLocation(): bool
    {
        return $this->delivery_latitude !== null && $this->delivery_longitude !== null;
    }
}
