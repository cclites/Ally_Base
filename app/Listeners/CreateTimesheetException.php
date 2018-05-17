<?php

namespace App\Listeners;

use App\Events\TimesheetCreated;
use Illuminate\Queue\InteractsWithQueue;
use App\SystemException;

class CreateTimesheetException
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
     * @param  TimesheetCreated  $event
     * @return void
     */
    public function handle(TimesheetCreated $event)
    {
        $description = "Caregiver {$event->timesheet->caregiver->name} has entered a timesheet for review.  Please review and confirm these shifts.  These shifts are not confirmed and will not appear in the Shift History Report until they are confirmed.  Please click 'View Timesheet' below to Confirm or Deny the Timesheet.";

        $exception = new SystemException([
            'title' => 'Manual Timesheet Submitted by ' . $event->timesheet->creator->name,
            'description' => $description,
            'reference_url' => route('business.timesheet', [$event->timesheet->id]),
            'business_id' => $event->timesheet->business_id,
        ]);

        $event->timesheet->exceptions()->save($exception);
    }
}
