<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get orders for the authenticated user or by email.
     */
    public function index(Request $request): JsonResponse
    {
        $email = $request->user()?->email ?? $request->query('email');

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email required for synchronization.',
            ], 400);
        }

        $orders = Order::query()
            ->where('customer_email', $email)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    /**
     * Store a new order.
     */
    public function store(Request $request, ShippingCalculator $shippingCalculator): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_number' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'status' => 'nullable|string|max:120',
                'payment_method' => 'nullable|string|max:120',
                'products' => 'required|array|min:1',
                'subtotal' => 'required|integer|min:0',
                'shipping_fee' => 'nullable|integer|min:0',
                'shipping_distance_km' => 'nullable|numeric|min:0',
                'shipping_courier' => 'nullable|string',
                'shipping_type' => 'nullable|string',
                'discount' => 'nullable|integer|min:0',
                'voucher_code' => 'nullable|string|max:120',
                'voucher_title' => 'nullable|string|max:255',
                'recipient_name' => 'nullable|string|max:255',
                'recipient_phone' => 'nullable|string|max:50',
                'address_label' => 'required|string|max:120',
                'address_detail' => 'required|string',
                'address_province' => 'nullable|string',
                'delivery_latitude' => 'nullable|numeric|between:-90,90',
                'delivery_longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $email = $request->user()?->email ?? $validated['customer_email'] ?? null;

            $discount = (int) ($validated['discount'] ?? 0);
            $shippingFee = (int) ($validated['shipping_fee'] ?? 0);
            $shippingDistance = $validated['shipping_distance_km'] ?? null;
            $shippingCourier = $validated['shipping_courier'] ?? null;
            $shippingType = $validated['shipping_type'] ?? null;

            if (($validated['delivery_latitude'] ?? null) !== null && ($validated['delivery_longitude'] ?? null) !== null) {
                $shipping = $shippingCalculator->calculate(
                    (float) $validated['delivery_latitude'],
                    (float) $validated['delivery_longitude'],
                    $validated['address_province'] ?? null
                );

                if ($shipping['is_available']) {
                    if ($shipping['shipping_type'] === 'local') {
                        $shippingFee = (int) $shipping['shipping_fee'];
                        $shippingCourier = 'Lokal';
                        $shippingType = 'local';
                    }
                    $shippingDistance = $shipping['distance_km'];
                }
            }

            $orderNumber = $validated['order_number'] ?? 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999);

            $saveData = [
                'customer_email' => $email,
                'status' => $validated['status'] ?? 'Menunggu Pembayaran',
                'shipping_fee' => $shippingFee,
                'shipping_distance_km' => $shippingDistance,
                'shipping_courier' => $shippingCourier,
                'shipping_type' => $shippingType,
                'discount' => $discount,
                'total' => (int) $validated['subtotal'] + $shippingFee - $discount,
            ];

            // Filter out fields that don't exist in the database yet
            $table = (new Order())->getTable();
            $finalData = [];
            foreach (array_merge($validated, $saveData) as $key => $value) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($table, $key)) {
                    $finalData[$key] = $value;
                }
            }

            $order = Order::query()->updateOrCreate(
                ['order_number' => $orderNumber],
                $finalData
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil disimpan.',
                'data' => $order,
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete an order (User confirms receipt).
     */
    public function completeOrder(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // Validasi status: Hanya boleh dari 'Dikirim'
        if ($order->status !== 'Dikirim') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan hanya dapat diselesaikan jika sudah dikirim.',
            ], 422);
        }

        $order->update(['status' => 'Selesai']);

        return response()->json([
            'status' => 'success',
            'message' => 'Terima kasih, pesanan telah dikonfirmasi diterima.',
            'data' => $order,
        ]);
    }
}
