<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoucherAd;
use Illuminate\Http\JsonResponse;

class VoucherAdController extends Controller
{
    public function show(): JsonResponse
    {
        $ad = VoucherAd::query()
            ->where('is_active', true)
            ->latest()
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $ad,
        ]);
    }
}
