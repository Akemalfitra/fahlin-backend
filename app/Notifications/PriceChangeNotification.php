<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceChangeNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $oldPrice;
    protected $newPrice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product, $oldPrice, $newPrice)
    {
        $this->product = $product;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
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
        $isDrop = $this->newPrice < $this->oldPrice;
        
        $title = $isDrop ? 'Harga Turun!' : 'Update Harga';
        $message = $isDrop 
            ? "Kabar gembira! Harga '{$this->product->name}' turun menjadi Rp " . number_format($this->newPrice, 0, ',', '.') . ". Sikat sekarang!"
            : "Harga '{$this->product->name}' telah diperbarui menjadi Rp " . number_format($this->newPrice, 0, ',', '.') . ".";

        return [
            'title' => $title,
            'message' => $message,
            'product_id' => $this->product->id,
            'image' => $this->product->image,
            'type' => 'price_change',
            'price_drop' => $isDrop,
            'click_action' => 'product_detail',
        ];
    }
}
