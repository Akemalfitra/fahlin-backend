<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->with('productImages')
            ->latest()
            ->get()
            ->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'images' => $product->images,
                    'image_options' => $product->productImages
                        ->map(fn ($image): array => [
                            'id' => $image->id,
                            'image' => $image->image_path,
                            'label' => $image->label,
                            'description' => $image->description,
                            'sort_order' => $image->sort_order,
                        ])
                        ->values()
                        ->all(),
                    'description' => $product->description,
                    'stock' => $product->stock,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Data Produk Fahlin Store',
            'data' => $products
        ], 200);
    }

    public function getBanners()
    {
        $banners = \App\Models\Banner::where('is_active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $banners
        ]);
    }
}
