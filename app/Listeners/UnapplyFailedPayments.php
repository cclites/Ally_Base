<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnapplyFailedPayments
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
     * @param \App\Events\PaymentFailed $event
     * @return void
     */
    public function handle(PaymentFailed $event)
    {
        $payment = $event->getPayment();
        foreach($payment->invoices as $invoice) {
            /** @var \App\Billing\ClientInvoice $invoice */
            $invoice->removePayment($payment);
        }
    }
}
