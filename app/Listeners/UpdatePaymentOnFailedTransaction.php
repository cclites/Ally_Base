<?php

namespace App\Listeners;

use App\Events\FailedTransaction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePaymentOnFailedTransaction
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
     * @param  FailedTransaction  $event
     * @return void
     */
    public function handle(FailedTransaction $event)
    {
        if ($payment = $event->transaction->payment) {
            $payment->update(['success' => 0]);

            foreach($payment->shifts as $shift) {
                $shift->statusManager()->ackReturnedPayment();
            }
        }
    }
}
