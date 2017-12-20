<?php
namespace App\Listeners;

use App\Contracts\ShiftEventInterface;

class CheckForClockOut
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
     * @param  ShiftEventInterface $event
     * @return void
     */
    public function handle(ShiftEventInterface $event)
    {
        $shift = $event->shift();
        if ($shift->checked_out_time && $shift->statusManager()->isClockedIn()) {
            $shift->statusManager()->ackClockOut($shift->verified);
        }
    }
}
