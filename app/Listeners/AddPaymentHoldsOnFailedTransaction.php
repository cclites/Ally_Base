<?php

namespace App\Listeners;

use App\Billing\Deposit;
use App\Events\FailedTransactionRecorded;
use App\Billing\Payment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddPaymentHoldsOnFailedTransaction
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
     * @param  \App\Events\FailedTransactionRecorded|\App\Events\FailedTransactionFound  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->transaction->payment) {
            $this->handlePayment($event->transaction->payment);
        }

        if ($event->transaction->deposit) {
            $this->handleDeposit($event->transaction->deposit);
        }
    }

    protected function handlePayment(Payment $payment)
    {
        if ($payment->client) {
            $payment->client->addHold("Payment {$payment->id} failed");
        }
        else if ($payment->business) {
            $payment->business->addHold("Payment {$payment->id} failed");
        }
    }

    protected function handleDeposit(Deposit $deposit)
    {
        if ($deposit->caregiver) {
            $deposit->caregiver->addHold("Deposit {$deposit->id} failed");
        }
        else if ($deposit->business) {
            $deposit->business->addHold("Deposit {$deposit->id} failed");
        }
    }
}
