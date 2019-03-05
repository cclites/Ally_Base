<?php

namespace App\Listeners;

use App\Events\DepositFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnapplyFailedDeposits
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\DepositFailed $event
     * @return void
     */
    public function handle(DepositFailed $event)
    {
        $deposit = $event->getDeposit();

        foreach($deposit->caregiverInvoices as $caregiverInvoice) {
            /** @var \App\Billing\CaregiverInvoice $caregiverInvoice */
            $caregiverInvoice->removeDeposit($deposit);
        }

        foreach($deposit->businessInvoices as $businessInvoice) {
            /** @var \App\Billing\BusinessInvoice $businessInvoice */
            $businessInvoice->removeDeposit($deposit);
        }
    }
}
