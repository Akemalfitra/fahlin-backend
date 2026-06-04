<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImagesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_api_returns_multiple_images_for_mobile_clients(): void
    {
        $product = Product::query()->create([
            'name' => 'Tas Model Baru',
            'price' => 150000,
            'description' => 'Produk dengan beberapa foto.',
            'stock' => 8,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image_path' => 'products/front.jpg',
            'label' => 'Hitam',
            'description' => 'Warna hitam glossy.',
            'sort_order' => 0,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image_path' => 'products/side.jpg',
            'label' => 'Pink',
            'description' => 'Warna pink pastel.',
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.image', 'products/front.jpg')
            ->assertJsonPath('data.0.images.0', 'products/front.jpg')
            ->assertJsonPath('data.0.images.1', 'products/side.jpg')
            ->assertJsonPath('data.0.image_options.0.label', 'Hitam')
            ->assertJsonPath('data.0.image_options.0.description', 'Warna hitam glossy.')
            ->assertJsonPath('data.0.image_options.1.label', 'Pink');
    }
}
