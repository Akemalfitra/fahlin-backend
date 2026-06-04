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
        Schema::table('messages', function (Blueprint $table) {
            // Menambahkan kolom product_id setelah user_id agar struktur tabel rapi
            $table->foreignId('product_id')
                  ->after('user_id') 
                  ->nullable()
                  ->constrained('products')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Menghapus foreign key terlebih dahulu baru menghapus kolomnya
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};