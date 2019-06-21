<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Log;

class SystemChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Create system notification
        if ($message = $notification->toSystem($notifiable)) {
            $message->save();
        }
    }
}
