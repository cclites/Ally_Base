<?php
namespace App\Shifts\Listeners;

use App\Billing\Events\InvoiceableDepositRemoved;
use App\Billing\Invoiceable\ShiftService;
use App\Shift;

class AcknowledgeShiftDepositFailure
{
    /**
     * Handle the event.
     *
     * @param InvoiceableDepositRemoved $event
     * @return void
     */
    public function handle(InvoiceableDepositRemoved $event)
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
        $shift->statusManager()->ackReturnedCaregiverDeposit();
    }

    private function handleBusinessDeposit(Shift $shift)
    {
        $shift->statusManager()->ackReturnedBusinessDeposit();

    }
}
