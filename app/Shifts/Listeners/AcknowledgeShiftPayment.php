<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceablePaymentAdded;
use App\Billing\Invoiceable\InvoiceableModel;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\InvoiceableQuery;
use App\Shift;

class AcknowledgeShiftPayment
{
    /**
     * Handle the event.
     *
     * @param  InvoiceablePaymentAdded  $event
     * @return void
     */
    public function handle(InvoiceablePaymentAdded $event)
    {
        if ($event->getInvoice()->getAmountDue() > 0) {
            return;
        }

        $invoiceable = $event->getInvoiceable();
        $shift = ($invoiceable instanceof ShiftService) ? $invoiceable->getShift() : $invoiceable;
        if ($shift instanceof Shift) {
            if ($this->query($shift)->hasClientInvoicesPaid()->exists()) {
                $shift->statusManager()->ackPayment();
            }
        }

    }

    private function query(InvoiceableModel $invoiceable)
    {
        return (new InvoiceableQuery($invoiceable))->forInvoiceable($invoiceable);
    }
}
