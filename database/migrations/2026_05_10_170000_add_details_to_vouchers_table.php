<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('code')->nullable()->unique()->after('title');
            $table->text('description')->nullable()->after('code');
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed')->after('description');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('min_purchase', 12, 2)->default(0)->after('discount_value');
            $table->decimal('max_discount', 12, 2)->nullable()->after('min_purchase');
            $table->unsignedInteger('quota')->nullable()->after('max_discount');
            $table->unsignedInteger('claimed_count')->default(0)->after('quota');
            $table->dateTime('starts_at')->nullable()->after('claimed_count');
            $table->dateTime('expires_at')->nullable()->after('starts_at');
            $table->boolean('is_active')->default(true)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'title',
                'code',
                'description',
                'discount_type',
                'discount_value',
                'min_purchase',
                'max_discount',
                'quota',
                'claimed_count',
                'starts_at',
                'expires_at',
                'is_active',
            ]);
        });
    }
};
