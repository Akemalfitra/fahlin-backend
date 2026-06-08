<?php

namespace App\Services;

use App\Models\ShippingSetting;
use InvalidArgumentException;

class ShippingCalculator
{
    public function calculate(?float $customerLatitude, ?float $customerLongitude, ?string $province = null): array
    {
        $setting = ShippingSetting::active();

        if ($setting->store_latitude === null || $setting->store_longitude === null) {
            throw new InvalidArgumentException('Lokasi toko belum diatur di admin.');
        }

        $distance = null;
        if ($customerLatitude !== null && $customerLongitude !== null) {
            $distance = $this->distanceInKilometers(
                (float) $setting->store_latitude,
                (float) $setting->store_longitude,
                $customerLatitude,
                $customerLongitude,
            );
        }

        // If it's local (only if we have distance)
        if ($distance !== null && $distance <= 50) {
            $freeDistance = (float) $setting->free_distance;
            $chargeableDistance = max(0, $distance - $freeDistance);
            
            $shippingFee = 0;
            if ($distance > $freeDistance) {
                $shippingFee = (int) $setting->base_fee + (int) ceil($chargeableDistance * (int) $setting->fee_per_km);
            }

            return [
                'distance_km' => round($distance, 2),
                'shipping_type' => 'local',
                'shipping_fee' => $shippingFee,
                'is_available' => true,
                'message' => 'Ongkir lokal berhasil dihitung.',
                'couriers' => [
                    [
                        'code' => 'lokal',
                        'name' => 'Pengiriman Lokal',
                        'rate' => $shippingFee,
                    ]
                ]
            ];
        }

        // If outside local or no coordinates
        if (!$province) {
            return [
                'distance_km' => $distance ? round($distance, 2) : 0,
                'shipping_type' => 'inter_province',
                'is_available' => false,
                'message' => 'Provinsi diperlukan untuk menghitung ongkir.',
            ];
        }

        return $this->calculateProvinceShipping($province, $distance);
    }

    private function calculateProvinceShipping(string $province, ?float $distance): array
    {
        $provinceRate = \App\Models\ProvinceShippingRate::where('province_name', 'like', "%$province%")
            ->where('is_active', true)
            ->first();

        if (!$provinceRate) {
            return [
                'distance_km' => $distance ? round($distance, 2) : 0,
                'shipping_type' => 'inter_province',
                'is_available' => false,
                'message' => "Maaf, pengiriman ke provinsi $province belum tersedia.",
            ];
        }

        return [
            'distance_km' => $distance ? round($distance, 2) : 0,
            'shipping_type' => 'inter_province',
            'is_available' => true,
            'message' => 'Ongkir antar provinsi berhasil didapatkan.',
            'couriers' => [
                [
                    'code' => 'ekspedisi',
                    'name' => 'Ekspedisi Reguler',
                    'rate' => (int) $provinceRate->shipping_rate,
                ],
            ]
        ];
    }

    private function distanceInKilometers(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latDistance = deg2rad($lat2 - $lat1);
        $lonDistance = deg2rad($lon2 - $lon1);

        $a = sin($latDistance / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDistance / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
