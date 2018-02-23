<?php

namespace App\Listeners;

use App\Deposit;
use App\Events\FailedTransactionRecorded;
use App\Payment;
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
     * @param  \App\Events\FailedTransactionRecorded  $event
     * @return void
     */
    public function handle(FailedTransactionRecorded $event)
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
            $payment->client->addHold();
        }
        else if ($payment->business) {
            $payment->business->addHold();
        }
    }

    protected function handleDeposit(Deposit $deposit)
    {
        if ($deposit->caregiver) {
            $deposit->caregiver->addHold();
        }
        else if ($deposit->business) {
            $deposit->business->addHold();
        }
    }
}
