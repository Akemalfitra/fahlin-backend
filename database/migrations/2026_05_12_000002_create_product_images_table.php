<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('label', 120);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('products')
            ->select(['id', 'image', 'images'])
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    $images = json_decode($product->images ?? '[]', true);
                    $images = array_values(array_filter(is_array($images) ? $images : []));

                    if (empty($images) && $product->image) {
                        $images = [$product->image];
                    }

                    foreach ($images as $index => $imagePath) {
                        DB::table('product_images')->insert([
                            'product_id' => $product->id,
                            'image_path' => $imagePath,
                            'label' => $index === 0 ? 'Utama' : 'Pilihan ' . ($index + 1),
                            'description' => null,
                            'sort_order' => $index,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
