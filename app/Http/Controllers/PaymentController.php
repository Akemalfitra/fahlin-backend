<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function getSnapToken(Request $request)
    {
        $validated = $request->validate([
            'total' => 'nullable|numeric|min:1',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'shipping_fee' => 'nullable|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.image_option_id' => 'nullable|integer|exists:product_images,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'order_number' => 'nullable|string',
        ]);

        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $itemDetails = [];
        $grossAmount = isset($validated['total']) ? (int) $validated['total'] : 0;

        if (! empty($validated['items'])) {
            $grossAmount = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $selectedImage = null;
                if (! empty($item['image_option_id'])) {
                    $selectedImage = ProductImage::query()
                        ->where('product_id', $product->id)
                        ->findOrFail($item['image_option_id']);
                }
                $quantity = (int) $item['quantity'];
                $grossAmount += $product->price * $quantity;
                $itemDetails[] = [
                    'id' => (string) ($selectedImage?->id ?? $product->id),
                    'price' => (int) $product->price,
                    'quantity' => $quantity,
                    'name' => $selectedImage
                        ? $product->name . ' - ' . $selectedImage->label
                        : $product->name,
                ];
            }
            $shippingFee = (int) ($validated['shipping_fee'] ?? 0);
            $discount = (int) ($validated['discount'] ?? 0);
            if ($shippingFee > 0) {
                $grossAmount += $shippingFee;
                $itemDetails[] = ['id' => 'shipping', 'price' => $shippingFee, 'quantity' => 1, 'name' => 'Ongkir'];
            }
            if ($discount > 0) {
                $grossAmount -= $discount;
                $itemDetails[] = ['id' => 'discount', 'price' => -$discount, 'quantity' => 1, 'name' => 'Voucher'];
            }
        }

        $customerName = $request->user()?->name ?? $validated['name'] ?? 'Customer';
        $customerEmail = $request->user()?->email ?? $validated['email'] ?? null;
        if (! $customerEmail) {
            return response()->json(['message' => 'Email customer tidak ditemukan.'], 422);
        }

        $orderId = $validated['order_number'] ?? 'FAHLIN-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
            ],
        ];

        if (! empty($itemDetails)) {
            $params['item_details'] = $itemDetails;
        }

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;
        $paymentType = $request->payment_type;

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            // Might be Midtrans internal order_id if client didn't sync yet
            Log::warning("Order not found for Midtrans callback: " . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus == 'capture') {
            if ($paymentType == 'credit_card') {
                if ($request->fraud_status == 'accept') {
                    $order->update(['status' => 'Diproses']);
                }
            }
        } else if ($transactionStatus == 'settlement') {
            $order->update(['status' => 'Diproses']);
        } else if ($transactionStatus == 'pending') {
            $order->update(['status' => 'Menunggu Pembayaran']);
        } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $order->update(['status' => 'Dibatalkan']);
        }

        return response()->json(['message' => 'Callback handled successfully']);
    }
}
