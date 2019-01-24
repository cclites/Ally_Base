<?php

namespace App\Listeners;

use App\Events\SmsThreadReplyCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Business\NewSmsReply;

class SendNewSmsReplyNotification implements ShouldQueue
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
     * @param  SmsThreadReplyCreated  $event
     * @return void
     */
    public function handle(SmsThreadReplyCreated $event)
    {
        if (! empty($event->reply->business)) {
            \Notification::send(
                $event->reply->business->usersToNotify(NewSmsReply::class),
                new NewSmsReply($event->reply)
            );
        }
    }
}
