<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table for Province Shipping Rates
        Schema::create('province_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('province_name')->unique();
            $table->unsignedInteger('jne_rate')->default(0);
            $table->unsignedInteger('jnt_rate')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add free_distance to shipping_settings
        Schema::table('shipping_settings', function (Blueprint $table) {
            $table->decimal('free_distance', 8, 2)->default(0)->after('fee_per_km');
        });

        // 3. Add province to user_addresses
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('province')->nullable()->after('full_address');
        });

        // 4. Add shipping info to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_courier')->nullable()->after('shipping_fee'); // e.g., 'Lokal', 'JNE', 'J&T'
            $table->string('shipping_type')->nullable()->after('shipping_courier'); // e.g., 'local', 'inter_province'
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_courier', 'shipping_type']);
        });

        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('province');
        });

        Schema::table('shipping_settings', function (Blueprint $table) {
            $table->dropColumn('free_distance');
        });

        Schema::dropIfExists('province_shipping_rates');
    }
};
