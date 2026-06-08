<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $isNew;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product, $isNew = true)
    {
        $this->product = $product;
        $this->isNew = $isNew;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->isNew ? 'Produk Baru!' : 'Update Produk',
            'message' => $this->isNew 
                ? "Produk baru '{$this->product->name}' telah tersedia. Cek sekarang!"
                : "Ada pembaruan pada produk '{$this->product->name}'.",
            'product_id' => $this->product->id,
            'image' => $this->product->image,
            'type' => 'product',
            'click_action' => 'product_detail',
        ];
    }
}
