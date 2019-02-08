<?php

namespace App\Listeners;

use App\Events\ShiftDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ShiftFlag;

class RecalculateDuplicateShiftFlags
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
     * @param  ShiftDeleted  $event
     * @return void
     */
    public function handle(ShiftDeleted $event)
    {
        foreach($event->shift->duplicates as $duplicate) {
            $duplicate->flagManager()->generate([ShiftFlag::DUPLICATE]);
        }
    }
}
