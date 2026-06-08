<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductNotification;
use App\Notifications\PriceChangeNotification;
use App\Services\FcmService;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $users = User::all();
        Notification::send($users, new ProductNotification($product, true));

        // Push Notification
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (!empty($tokens)) {
            FcmService::sendToMultiple(
                $tokens, 
                'Produk Baru!', 
                "Produk baru '{$product->name}' telah tersedia. Cek sekarang!",
                ['type' => 'product', 'product_id' => $product->id, 'click_action' => 'product_detail']
            );
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if ($product->wasChanged('price')) {
            $oldPrice = $product->getOriginal('price');
            $newPrice = $product->price;
            
            $users = User::all();
            Notification::send($users, new PriceChangeNotification($product, $oldPrice, $newPrice));

            // Push Notification
            $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($tokens)) {
                $isDrop = $newPrice < $oldPrice;
                $title = $isDrop ? 'Harga Turun!' : 'Update Harga';
                $message = $isDrop 
                    ? "Kabar gembira! Harga '{$product->name}' turun menjadi Rp " . number_format($newPrice, 0, ',', '.') . ". Sikat sekarang!"
                    : "Harga '{$product->name}' telah diperbarui menjadi Rp " . number_format($newPrice, 0, ',', '.') . ".";

                FcmService::sendToMultiple(
                    $tokens, 
                    $title, 
                    $message,
                    ['type' => 'price_change', 'product_id' => $product->id, 'click_action' => 'product_detail']
                );
            }
        }
    }
}
