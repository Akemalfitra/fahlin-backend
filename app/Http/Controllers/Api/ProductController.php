<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'productImages'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Produk Fahlin Store',
            'data' => ProductResource::collection($products),
        ], 200);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'productImages']);

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product),
        ]);
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
