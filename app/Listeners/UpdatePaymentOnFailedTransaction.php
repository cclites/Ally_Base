<?php

namespace App\Listeners;

use App\Events\FailedTransactionRecorded;
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
     * @param  \App\Events\FailedTransactionRecorded  $event
     * @return void
     */
    public function handle(FailedTransactionRecorded $event)
    {
        if ($payment = $event->transaction->payment) {
            $payment->update(['success' => 0]);

            $shifts = $payment->shifts;
            foreach($shifts as $shift) {
                $shift->statusManager()->ackReturnedPayment();
                $shift->update(['payment_id' => null]);
            }
        }
    }
}
