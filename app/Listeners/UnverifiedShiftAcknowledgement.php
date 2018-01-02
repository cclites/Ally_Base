<?php

namespace App\Listeners;

use App\Events\UnverifiedShiftConfirmed;
use App\SystemException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class UnverifiedShiftAcknowledgement
 * Acknowledges a shift's related exception automatically if a shift is approved
 *
 * @package App\Listeners
 */
class UnverifiedShiftAcknowledgement
{
    /**
     * Handle the event.
     *
     * @param  UnverifiedShiftConfirmed  $event
     * @return void
     */
    public function handle(UnverifiedShiftConfirmed $event)
    {
        if ($event->shift->exceptions->count())
        {
            $event->shift->exceptions->each(function(SystemException $exception) {
                $titleMatch = 'Unverified Shift';
                if (!$exception->acknowledged_at && substr($exception->title, 0, strlen($titleMatch)) === $titleMatch) {
                    $exception->acknowledge('Automatic acknowledgement caused by shift confirmation.');
                }
            });
        }
    }
}
