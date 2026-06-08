<?php

namespace App\Observers;

use App\Models\Voucher;
use App\Models\User;
use App\Notifications\VoucherNotification;
use App\Services\FcmService;
use Illuminate\Support\Facades\Notification;

class VoucherObserver
{
    /**
     * Handle the Voucher "created" event.
     */
    public function created(Voucher $voucher): void
    {
        if ($voucher->is_active) {
            $users = User::all();
            Notification::send($users, new VoucherNotification($voucher));

            // Push Notification
            $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($tokens)) {
                FcmService::sendToMultiple(
                    $tokens, 
                    'Voucher Baru!', 
                    "Voucher '{$voucher->title}' sudah bisa diklaim. Gunakan kode '{$voucher->code}'!",
                    ['type' => 'voucher', 'voucher_id' => $voucher->id, 'click_action' => 'voucher_list']
                );
            }
        }
    }

    /**
     * Handle the Voucher "updated" event.
     */
    public function updated(Voucher $voucher): void
    {
        if ($voucher->wasChanged('is_active') && $voucher->is_active) {
            $users = User::all();
            Notification::send($users, new VoucherNotification($voucher));

            // Push Notification
            $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($tokens)) {
                FcmService::sendToMultiple(
                    $tokens, 
                    'Voucher Baru!', 
                    "Voucher '{$voucher->title}' sudah bisa diklaim!",
                    ['type' => 'voucher', 'voucher_id' => $voucher->id, 'click_action' => 'voucher_list']
                );
            }
        }
    }
}
