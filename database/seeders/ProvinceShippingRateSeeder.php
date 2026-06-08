<?php

namespace Database\Seeders;

use App\Models\ProvinceShippingRate;
use Illuminate\Database\Seeder;

class ProvinceShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            // Java (Pusat)
            'DKI Jakarta' => 10000,
            'Jawa Barat' => 12000,
            'Banten' => 12000,
            'Jawa Tengah' => 18000,
            'DI Yogyakarta' => 18000,
            'Jawa Timur' => 22000,

            // Sumatera
            'Lampung' => 20000,
            'Sumatera Selatan' => 25000,
            'Bengkulu' => 30000,
            'Jambi' => 30000,
            'Kepulauan Bangka Belitung' => 30000,
            'Riau' => 35000,
            'Sumatera Barat' => 35000,
            'Kepulauan Riau' => 40000,
            'Sumatera Utara' => 45000,
            'Aceh' => 50000,

            // Bali & Nusa Tenggara
            'Bali' => 25000,
            'Nusa Tenggara Barat' => 35000,
            'Nusa Tenggara Timur' => 55000,

            // Kalimantan
            'Kalimantan Barat' => 40000,
            'Kalimantan Selatan' => 40000,
            'Kalimantan Tengah' => 45000,
            'Kalimantan Timur' => 45000,
            'Kalimantan Utara' => 55000,

            // Sulawesi
            'Sulawesi Selatan' => 45000,
            'Sulawesi Tenggara' => 55000,
            'Sulawesi Barat' => 55000,
            'Sulawesi Tengah' => 55000,
            'Sulawesi Utara' => 65000,
            'Gorontalo' => 65000,

            // Maluku & Papua
            'Maluku' => 75000,
            'Maluku Utara' => 80000,
            'Papua Barat Daya' => 90000,
            'Papua Barat' => 90000,
            'Papua Selatan' => 95000,
            'Papua Tengah' => 95000,
            'Papua' => 100000,
            'Papua Pegunungan' => 115000,
        ];

        foreach ($provinces as $province => $rate) {
            ProvinceShippingRate::updateOrCreate(
                ['province_name' => $province],
                [
                    'shipping_rate' => $rate,
                    'is_active' => true,
                ]
            );
        }
    }
}
