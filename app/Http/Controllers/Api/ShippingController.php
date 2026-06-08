<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ShippingController extends Controller
{
    public function calculate(Request $request, ShippingCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'province' => 'nullable|string',
        ]);

        try {
            $result = $calculator->calculate(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $validated['province'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => $result['is_available'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result,
        ], $result['is_available'] ? 200 : 422);
    }
}
