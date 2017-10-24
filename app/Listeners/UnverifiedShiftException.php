<?php

namespace App\Listeners;

use App\Events\UnverifiedShiftCreated;
use App\SystemException;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class UnverifiedShiftException
 * Creates an exception when an unverified shift is created (clocked out)
 *
 * @package App\Listeners
 */
class UnverifiedShiftException
{

    /**
     * Handle the event.
     *
     * @param  UnverifiedShiftCreated  $event
     * @return void
     */
    public function handle(UnverifiedShiftCreated $event)
    {
        $shift = $event->shift;
        $business = $shift->business;
        $checked_in_time = local_date($shift->checked_in_time, 'm/d/Y h:iA', $business->timezone);
        $checked_out_time = local_date($shift->checked_out_time, 'm/d/Y h:iA', $business->timezone);
        $description = $shift->caregiver->name() . "'s shift for " . $shift->client->name() .
            "clocked in at $checked_in_time and clocked out at $checked_out_time was NOT verified through our geolocation or telefony services. " .
            "You will need to manually approve this shift to qualify it for payment.";

        $exception = new SystemException([
            'title' => 'Unverified Shift for ' . $shift->caregiver->name(),
            'description' => $description,
            'reference_url' => route('business.shifts.show', [$shift->id]),
            'business_id' => $business->id,
        ]);

        $shift->exceptions()->save($exception);
    }
}
