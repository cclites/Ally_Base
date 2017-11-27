<?php

namespace App\Providers;

use App\Events\FailedTransaction;
use App\Events\ShiftModified;
use App\Events\UnverifiedShiftApproved;
use App\Events\UnverifiedShiftCreated;
use App\Listeners\PostToSlackOnFailedTransaction;
use App\Listeners\ShiftStatusUpdate;
use App\Listeners\UnverifiedShiftAcknowledgement;
use App\Listeners\UnverifiedShiftException;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UnverifiedShiftCreated::class => [
            UnverifiedShiftException::class,
        ],
        UnverifiedShiftApproved::class => [
            UnverifiedShiftAcknowledgement::class,
        ],
        ShiftModified::class => [
            ShiftStatusUpdate::class,
        ],
        FailedTransaction::class => [
            PostToSlackOnFailedTransaction::class,
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
