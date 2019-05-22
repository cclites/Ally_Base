<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceableInvoiced;
use App\Billing\Events\InvoiceableUninvoiced;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

class AcknowledgeShiftUninvoiced
{
    /**
     * Handle the event.
     *
     * @param InvoiceableUninvoiced $event
     * @return void
     */
    public function handle(InvoiceableUninvoiced $event)
    {
        $invoiceable = $event->getInvoiceable();
        $shift = ($invoiceable instanceof ShiftService) ? $invoiceable->getShift() : $invoiceable;
        if ($shift instanceof Shift) {
            $shift->statusManager()->ackClientInvoiceDeleted();
        }
    }
}
