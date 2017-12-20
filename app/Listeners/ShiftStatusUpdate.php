<?php
namespace App\Listeners;

use App\Contracts\ShiftEventInterface;
use App\ShiftStatusHistory;

class ShiftStatusUpdate
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
        $lastStatusHistory = $event->shift()->statusHistory()->orderBy('id', 'DESC')->first();
        if (!$lastStatusHistory || $lastStatusHistory->new_status !== $event->shift()->status) {
            $statusHistory = new ShiftStatusHistory(['new_status' => $event->shift()->status]);
            $event->shift()->statusHistory()->save($statusHistory);
        }
    }
}
