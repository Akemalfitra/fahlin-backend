<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultCategory = Category::query()->firstOrCreate(
            ['slug' => 'umum'],
            ['name' => 'Umum', 'description' => 'Kategori default produk lama.']
        );

        Schema::table('products', function (Blueprint $table) use ($defaultCategory): void {
            $table->foreignId('category_id')
                ->default($defaultCategory->id)
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
