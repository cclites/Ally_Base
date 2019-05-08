<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceableInvoiced;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

class AcknowledgeShiftInvoice
{
    /**
     * Handle the event.
     *
     * @param  InvoiceableInvoiced  $event
     * @return void
     */
    public function handle(InvoiceableInvoiced $event)
    {
        $invoiceable = $event->getInvoiceable();
        $shift = ($invoiceable instanceof ShiftService) ? $invoiceable->getShift() : $invoiceable;
        if ($shift instanceof Shift) {
            $shift->statusManager()->ackClientInvoice();
        }
    }
}
