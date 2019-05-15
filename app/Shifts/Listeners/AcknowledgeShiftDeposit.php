<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceableDepositAdded;
use App\Billing\Invoiceable\InvoiceableModel;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\InvoiceableQuery;
use App\Shift;

class AcknowledgeShiftDeposit
{
    /**
     * Handle the event.
     *
     * @param InvoiceableDepositAdded $event
     * @return void
     */
    public function handle(InvoiceableDepositAdded $event)
    {
        $deposit = $event->getDeposit();
        $invoiceable = $event->getInvoiceable();

        $shift = ($invoiceable instanceof ShiftService) ? $invoiceable->getShift() : $invoiceable;
        if ($shift instanceof Shift) {
            switch($deposit->deposit_type) {
                case 'business':
                    $this->handleBusinessDeposit($shift);
                    return;
                case 'caregiver':
                    $this->handleCaregiverDeposit($shift);
                    return;
            }
        }
    }

    private function handleCaregiverDeposit(Shift $shift)
    {
        if ($this->query($shift)->hasCaregiverInvoicesPaid()->exists()) {
            $shift->statusManager()->ackCaregiverDeposit();
        }
    }

    private function handleBusinessDeposit(Shift $shift)
    {
        if ($this->query($shift)->hasBusinessInvoicesPaid()->exists()) {
            $shift->statusManager()->ackBusinessDeposit();
        }
    }

    private function query(InvoiceableModel $invoiceable)
    {
        return (new InvoiceableQuery($invoiceable))->forInvoiceable($invoiceable);
    }
}
