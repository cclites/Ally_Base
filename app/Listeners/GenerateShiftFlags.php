<?php

namespace App\Listeners;

use App\Events\ShiftFlagsCouldChange;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateShiftFlags
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
     * @param  ShiftFlagsCouldChange  $event
     * @return void
     */
    public function handle(ShiftFlagsCouldChange $event)
    {
        $event->shift->flagManager()->generate();
    }
}
