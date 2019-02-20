<?php

namespace App\Providers;

use App\Events\BusinessChainCreated;
use App\Events\FailedTransactionFound;
use App\Events\FailedTransactionRecorded;
use App\Events\ShiftCreated;
use App\Events\ShiftModified;
use App\Events\UnverifiedShiftConfirmed;
use App\Events\UnverifiedShiftCreated;
use App\Events\UnverifiedShiftLocation;
use App\Listeners\AddPaymentHoldsOnFailedTransaction;
use App\Listeners\CheckForClockOut;
use App\Listeners\CreateDefaultService;
use App\Listeners\PostToSlackOnFailedTransaction;
use App\Listeners\ShiftStatusUpdate;
use App\Listeners\UnverifiedShiftAcknowledgement;
use App\Listeners\UnverifiedLocationException;
use App\Listeners\UpdateDepositOnFailedTransaction;
use App\Listeners\UpdatePaymentOnFailedTransaction;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TimesheetCreated;
use App\Listeners\CreateTimesheetException;
use App\Events\TaskAssigned;
use App\Listeners\SendAssignedTaskEmail;
use App\Events\ShiftFlagsCouldChange;
use App\Listeners\GenerateShiftFlags;
use App\Events\ShiftDeleted;
use App\Listeners\RecalculateDuplicateShiftFlags;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        BusinessChainCreated::class => [
            CreateDefaultService::class,
        ],
        TaskAssigned::class => [
//            SendAssignedTaskEmail::class,
        ],
        UnverifiedShiftLocation::class   => [
            UnverifiedLocationException::class,
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
        FailedTransactionFound::class   => [
            PostToSlackOnFailedTransaction::class,
            AddPaymentHoldsOnFailedTransaction::class,
        ],
        FailedTransactionRecorded::class => [
            UpdateDepositOnFailedTransaction::class,
            UpdatePaymentOnFailedTransaction::class,
            AddPaymentHoldsOnFailedTransaction::class,
        ],
        TimesheetCreated::class => [
            CreateTimesheetException::class,
        ],
        ShiftFlagsCouldChange::class => [
            GenerateShiftFlags::class,
        ],
        ShiftDeleted::class => [
            RecalculateDuplicateShiftFlags::class,
        ],
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
