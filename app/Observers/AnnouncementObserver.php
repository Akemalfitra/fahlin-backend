<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Services\FcmService;
use Illuminate\Support\Facades\Notification;

class AnnouncementObserver
{
    /**
     * Handle the Announcement "created" event.
     */
    public function created(Announcement $announcement): void
    {
        $users = User::all();
        Notification::send($users, new AnnouncementNotification($announcement));

        // Push Notification
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (!empty($tokens)) {
            FcmService::sendToMultiple(
                $tokens, 
                $announcement->title, 
                $announcement->content,
                ['type' => 'announcement', 'announcement_id' => $announcement->id, 'click_action' => 'announcement_detail']
            );
        }
    }
}
