<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('user_addresses', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (!Schema::hasColumn('user_addresses', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('user_addresses', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('district');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'address_province')) {
                $table->string('address_province')->nullable()->after('address_detail');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['city', 'district', 'postal_code']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('address_province');
        });
    }
};
