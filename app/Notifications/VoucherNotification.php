<?php

namespace App\Notifications;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoucherNotification extends Notification
{
    use Queueable;

    protected $voucher;

    /**
     * Create a new notification instance.
     */
    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
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
            'title' => 'Voucher Baru!',
            'message' => "Voucher '{$this->voucher->title}' sudah bisa diklaim. Gunakan kode '{$this->voucher->code}' untuk mendapatkan diskon!",
            'voucher_id' => $this->voucher->id,
            'type' => 'voucher',
            'click_action' => 'voucher_list',
        ];
    }
}
