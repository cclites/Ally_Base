<?php

namespace App\Providers;

use App\Billing\Events\InvoiceableDepositAdded;
use App\Billing\Events\InvoiceableDepositRemoved;
use App\Billing\Events\InvoiceableInvoiced;
use App\Billing\Events\InvoiceablePaymentAdded;
use App\Billing\Events\InvoiceablePaymentRemoved;
use App\Billing\Events\InvoiceableUninvoiced;
use App\Events\BusinessChainCreated;
use App\Events\ClientCreated;
use App\Events\DepositFailed;
use App\Events\FailedTransactionFound;
use App\Events\FailedTransactionRecorded;
use App\Events\PaymentFailed;
use App\Events\ShiftCreated;
use App\Events\ShiftModified;
use App\Events\UnverifiedShiftConfirmed;
use App\Listeners\AddPaymentHoldsOnFailedTransaction;
use App\Listeners\CheckForClockOut;
use App\Listeners\CreateDefaultClientPayer;
use App\Listeners\CreateDefaultService;
use App\Listeners\PostToSlackOnFailedTransaction;
use App\Listeners\ShiftStatusUpdate;
use App\Listeners\UnapplyFailedDeposits;
use App\Listeners\UnapplyFailedPayments;
use App\Listeners\UnverifiedShiftAcknowledgement;
use App\Listeners\UpdateDepositOnFailedTransaction;
use App\Listeners\UpdatePaymentOnFailedTransaction;
use App\Shifts\Listeners\AcknowledgeShiftDeposit;
use App\Shifts\Listeners\AcknowledgeShiftDepositFailure;
use App\Shifts\Listeners\AcknowledgeShiftInvoice;
use App\Shifts\Listeners\AcknowledgeShiftPayment;
use App\Shifts\Listeners\AcknowledgeShiftPaymentFailure;
use App\Shifts\Listeners\AcknowledgeShiftUninvoiced;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TimesheetCreated;
use App\Events\TaskAssigned;
use App\Listeners\SendAssignedTaskEmail;
use App\Listeners\SendManualTimesheetNotification;
use App\Listeners\SendUnverifiedShiftNotification;
use App\Events\UnverifiedClockOut;
use App\Events\UnverifiedClockIn;
use App\Events\SmsThreadReplyCreated;
use App\Listeners\SendNewSmsReplyNotification;
use App\Events\ShiftFlagsCouldChange;
use App\Listeners\GenerateShiftFlags;
use App\Events\ShiftDeleted;
use App\Listeners\RecalculateDuplicateShiftFlags;
use App\Listeners\HandleSmsAutoReply;

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
        ClientCreated::class => [
            CreateDefaultClientPayer::class,
        ],
        DepositFailed::class => [
            UnapplyFailedDeposits::class,
        ],
        FailedTransactionFound::class => [
            PostToSlackOnFailedTransaction::class,
            AddPaymentHoldsOnFailedTransaction::class,
        ],
        FailedTransactionRecorded::class => [
            AddPaymentHoldsOnFailedTransaction::class,
        ],
        InvoiceableDepositAdded::class => [
            AcknowledgeShiftDeposit::class,
        ],
        InvoiceableDepositRemoved::class => [
            AcknowledgeShiftDepositFailure::class,
        ],
        InvoiceableInvoiced::class => [
            AcknowledgeShiftInvoice::class,
        ],
        InvoiceablePaymentAdded::class => [
            AcknowledgeShiftPayment::class,
        ],
        InvoiceablePaymentRemoved::class => [
            AcknowledgeShiftPaymentFailure::class,
        ],
        InvoiceableUninvoiced::class => [
            AcknowledgeShiftUninvoiced::class,
        ],
        PaymentFailed::class => [
            UnapplyFailedPayments::class,
        ],
        ShiftModified::class => [
            ShiftStatusUpdate::class,
            CheckForClockOut::class,
        ],
        ShiftCreated::class => [
            ShiftStatusUpdate::class,
            CheckForClockOut::class,
        ],
        ShiftFlagsCouldChange::class => [
            GenerateShiftFlags::class,
        ],
        ShiftDeleted::class => [
            RecalculateDuplicateShiftFlags::class,
        ],
        SmsThreadReplyCreated::class => [
            SendNewSmsReplyNotification::class,
            HandleSmsAutoReply::class,
        ],
        TaskAssigned::class => [
//            SendAssignedTaskEmail::class,
        ],
        TimesheetCreated::class => [
            SendManualTimesheetNotification::class,
        ],
        UnverifiedShiftConfirmed::class => [
            UnverifiedShiftAcknowledgement::class,
        ],
        UnverifiedClockIn::class => [
            SendUnverifiedShiftNotification::class,
        ],
        UnverifiedClockOut::class => [
            SendUnverifiedShiftNotification::class,
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
