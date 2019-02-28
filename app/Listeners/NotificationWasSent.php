<?php

namespace App\Listeners;

use App\Channels\SmsChannel;
use App\CommunicationLog;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationWasSent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->channel == 'mail') {
            CommunicationLog::create([
//                'business_id' => $event->notifiable->business_id,
                'user_id' => $event->notifiable->id,
                'channel' => $event->channel,
                'message' => '',
                'phone' => null,
                'email' => $event->notifiable->email,
                'sent_at' => Carbon::now(),
            ]);
        } else if ($event->channel == SmsChannel::class) {

        }
        // public function __construct($notifiable, $notification, $channel, $response = null)
    }
}
