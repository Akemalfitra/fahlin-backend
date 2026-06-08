<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table): void {
            $table->string('block')->nullable()->after('full_address');
            $table->string('house_number')->nullable()->after('block');
            $table->string('landmark')->nullable()->after('house_number');
            $table->text('note')->nullable()->after('landmark');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table): void {
            $table->dropColumn(['block', 'house_number', 'landmark', 'note']);
        });
    }
};
