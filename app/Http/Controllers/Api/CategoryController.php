<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function products(Category $category): JsonResponse
    {
        $products = $category->products()
            ->with(['category', 'productImages'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
        ]);
    }
}
