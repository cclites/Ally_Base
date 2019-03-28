<?php

namespace App\Listeners;

use App\Notifications\Business\UnverifiedShift;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\UnverifiedShiftConfirmed;
use App\SystemNotification;

/**
 * Class UnverifiedShiftAcknowledgement
 *
 * @package App\Listeners
 */
class UnverifiedShiftAcknowledgement
{
    /**
     * Acknowledges a shift's related notifications automatically if a shift is approved
     *
     * @param  UnverifiedShiftConfirmed  $event
     * @return void
     */
    public function handle(UnverifiedShiftConfirmed $event)
    {
        if ($event->shift->systemNotifications()->count())
        {
            $event->shift->systemNotifications()->where('key', UnverifiedShift::getKey())
                ->each(function(SystemNotification $notification) {
                    if (! $notification->acknowledged_at) {
                        $notification->acknowledge('Automatic acknowledgement caused by shift confirmation.');
                    }
                });
        }
    }
}
