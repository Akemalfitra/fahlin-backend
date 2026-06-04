<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
// Kamu tidak wajib menulis 'use Midtrans\Config;' karena sudah pakai backslash (\Midtrans) di bawah

class PaymentController extends Controller
{
    public function getSnapToken(Request $request)
    {
        $validated = $request->validate([
            'total' => 'nullable|numeric|min:1',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.image_option_id' => 'nullable|integer|exists:product_images,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        // 1. Konfigurasi Midtrans
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
        }

        if ($grossAmount < 1) {
            return response()->json([
                'message' => 'Total pembayaran atau items checkout wajib dikirim.',
            ], 422);
        }

        $customerName = $request->user()?->name
            ?? $validated['name']
            ?? 'Customer';
        $customerEmail = $request->user()?->email
            ?? $validated['email']
            ?? null;

        if (! $customerEmail) {
            return response()->json([
                'message' => 'Email customer tidak ditemukan. Silakan login ulang.',
            ], 422);
        }

        // 2. Parameter Transaksi
        $params = [
            'transaction_details' => [
                'order_id' => 'FAHLIN-' . time(), 
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

        // 3. Proses mendapatkan Token
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
