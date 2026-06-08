<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('province_shipping_rates', function (Blueprint $table) {
            $table->unsignedInteger('shipping_rate')->default(0)->after('province_name');
            $table->dropColumn(['jne_rate', 'jnt_rate']);
        });
    }

    public function down(): void
    {
        Schema::table('province_shipping_rates', function (Blueprint $table) {
            $table->unsignedInteger('jne_rate')->default(0)->after('province_name');
            $table->unsignedInteger('jnt_rate')->default(0)->after('jne_rate');
            $table->dropColumn('shipping_rate');
        });
    }
};
