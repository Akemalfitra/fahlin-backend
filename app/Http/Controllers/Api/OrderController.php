<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|string|max:255|unique:orders,order_number',
            'status' => 'nullable|string|max:120',
            'products' => 'required|array|min:1',
            'subtotal' => 'required|integer|min:0',
            'shipping_fee' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'voucher_code' => 'nullable|string|max:120',
            'voucher_title' => 'nullable|string|max:255',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'address_label' => 'required|string|max:120',
            'address_detail' => 'required|string',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $discount = (int) ($validated['discount'] ?? 0);

        $order = Order::query()->create([
            ...$validated,
            'order_number' => $validated['order_number'] ?? 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'status' => $validated['status'] ?? 'Menunggu Pembayaran',
            'discount' => $discount,
            'total' => (int) $validated['subtotal'] + (int) $validated['shipping_fee'] - $discount,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil disimpan.',
            'data' => $order,
        ], 201);
    }
}
