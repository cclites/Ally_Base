<?php

namespace App\Providers;

use App\Events\FailedTransaction;
use App\Events\ShiftCreated;
use App\Events\ShiftModified;
use App\Events\UnverifiedShiftConfirmed;
use App\Events\UnverifiedShiftCreated;
use App\Listeners\CheckForClockOut;
use App\Listeners\PostToSlackOnFailedTransaction;
use App\Listeners\ShiftStatusUpdate;
use App\Listeners\UnverifiedShiftAcknowledgement;
use App\Listeners\UnverifiedShiftException;
use App\Listeners\UpdateDepositOnFailedTransaction;
use App\Listeners\UpdatePaymentOnFailedTransaction;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UnverifiedShiftCreated::class   => [
            UnverifiedShiftException::class,
        ],
        UnverifiedShiftConfirmed::class => [
            UnverifiedShiftAcknowledgement::class,
        ],
        ShiftModified::class            => [
            ShiftStatusUpdate::class,
            CheckForClockOut::class,
        ],
        ShiftCreated::class             => [
            ShiftStatusUpdate::class,
            CheckForClockOut::class,
        ],
        FailedTransaction::class        => [
            PostToSlackOnFailedTransaction::class,
            UpdateDepositOnFailedTransaction::class,
            UpdatePaymentOnFailedTransaction::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
