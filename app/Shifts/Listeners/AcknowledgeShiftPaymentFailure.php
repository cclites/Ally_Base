<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceablePaymentRemoved;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

class AcknowledgeShiftPaymentFailure
{
    /**
     * Handle the event.
     *
     * @param InvoiceablePaymentRemoved $event
     * @return void
     */
    public function handle(InvoiceablePaymentRemoved $event)
    {
        if ($event->getInvoice()->getAmountDue() == 0) {
            return;
        }

        $invoiceable = $event->getInvoiceable();
        $shift = ($invoiceable instanceof ShiftService) ? $invoiceable->getShift() : $invoiceable;
        if ($shift instanceof Shift) {
            $shift->statusManager()->ackReturnedPayment();
        }

    }
}
