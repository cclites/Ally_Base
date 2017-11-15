<?php

namespace App\Listeners;

use App\Events\ShiftModified;
use App\Events\UnverifiedShiftApproved;
use App\Shift;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     * @param  ShiftModified  $event
     * @return void
     */
    public function handle(ShiftModified $event)
    {
        if ($event->shift->status === Shift::WAITING_FOR_APPROVAL) {
            $event->shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
        }
        else if ($event->shift->status === Shift::WAITING_FOR_CHARGE) {
            if (auth()->user()->role_type !== 'admin') {
                $event->shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
            }
        }
        else if ($event->shift->status === Shift::CLOCKED_IN || $event->shift->status === null) {
            if ($event->shift->checked_out_time) {
                if ($event->shift->verified) {
                    $event->shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
                }
                else {
                    $event->shift->update(['status' => Shift::WAITING_FOR_APPROVAL]);
                }
            }
        }
    }
}
