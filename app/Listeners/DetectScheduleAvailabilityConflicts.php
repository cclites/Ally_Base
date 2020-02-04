<?php

namespace App\Listeners;

use App\Caregiver;
use App\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\Business\CaregiverAvailabilityChanged;
use phpDocumentor\Reflection\Types\Boolean;


class DetectScheduleAvailabilityConflicts
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
    public function handle(CaregiverAvailabilityChanged $event)
    {
        \Log::info("DetectScheduledAvailability");
        \Notification::send($event->caregiver->business->notifiableUsers(),new CaregiverAvailabilityChanged($event->caregiver));
    }

}
