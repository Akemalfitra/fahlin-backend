<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('base_fee')->default(3000);
            $table->unsignedInteger('fee_per_km')->default(2000);
            $table->decimal('max_radius', 8, 2)->default(20);
            $table->decimal('store_latitude', 10, 7)->nullable();
            $table->decimal('store_longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        DB::table('shipping_settings')->insert([
            'base_fee' => 3000,
            'fee_per_km' => 2000,
            'max_radius' => 20,
            'store_latitude' => null,
            'store_longitude' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
