<?php

namespace App\Listeners;

use App\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\CaregiverAvailabilityChanged;
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
        //TODO: send notification
    }

}
