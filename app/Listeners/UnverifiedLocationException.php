<?php

namespace App\Listeners;

use App\Events\UnverifiedShiftLocation;
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
class UnverifiedLocationException
{

    /**
     * Handle the event.
     *
     * @param  UnverifiedShiftLocation  $event
     * @return void
     */
    public function handle(UnverifiedShiftLocation $event)
    {
        $shift = $event->shift;
        $business = $shift->business;

        if (!$business->location_exceptions) {
            // Business has location exceptions disabled
            return;
        }

        $checked_in_time = local_date($shift->checked_in_time, 'm/d/Y g:iA', $business->timezone);
        $checked_out_time = local_date($shift->checked_out_time, 'm/d/Y g:iA', $business->timezone);
        $description = $shift->caregiver->name() . "'s shift for " . $shift->client->name() .
            " clocked in at $checked_in_time and clocked out at $checked_out_time was unable to be verified through our geolocation services." .
            " This shift is 'unconfirmed'.  You may click 'View Shift' below or see the Shift History Report to confirm it and qualify it for payment.";

        $exception = new SystemException([
            'title' => 'Unverified Shift ' . $shift->caregiver->name() . ' - Geolocation Failed',
            'description' => $description,
            'reference_url' => route('business.shifts.show', [$shift->id]),
            'business_id' => $business->id,
        ]);

        $shift->exceptions()->save($exception);
    }
}
