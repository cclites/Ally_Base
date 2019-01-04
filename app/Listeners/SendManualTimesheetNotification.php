<?php

namespace App\Listeners;

use App\Events\TimesheetCreated;
use App\Notifications\Business\ManualTimesheet;

class SendManualTimesheetNotification
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
        // Only send notifications when the Caregiver is the
        // one who submitted the Timesheet.
        if ($event->timesheet->creator_id == $event->timesheet->caregiver_id) {
            $users = $event->timesheet->business->usersToNotify(ManualTimesheet::class);

            \Notification::send($users, new ManualTimesheet($event->timesheet));
        }
    }
}
