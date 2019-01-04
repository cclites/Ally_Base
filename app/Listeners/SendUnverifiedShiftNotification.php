<?php

namespace App\Listeners;

use App\Events\UnverifiedShiftLocation;
use App\Notifications\Business\UnverifiedShift;

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
     * @param  UnverifiedShiftLocation  $event
     * @return void
     */
    public function handle(UnverifiedShiftLocation $event)
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
