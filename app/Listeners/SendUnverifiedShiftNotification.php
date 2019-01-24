<?php

namespace App\Listeners;

use App\Notifications\Business\UnverifiedShift;
use App\Events\UnverifiedClockOut;

class SendUnverifiedShiftNotification
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
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)
    {
        $shift = $event->shift;
        $business = $shift->business;

        if (! $business->location_exceptions) {
            // Business has location exceptions disabled
            return;
        }

        $users = $business->usersToNotify(UnverifiedShift::class);
        \Notification::send($users, new UnverifiedShift($shift));
    }
}
