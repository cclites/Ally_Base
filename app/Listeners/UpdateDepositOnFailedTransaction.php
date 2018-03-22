<?php

namespace App\Listeners;

use App\Events\FailedTransactionRecorded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateDepositOnFailedTransaction
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
        if ($deposit = $event->transaction->deposit) {
            $deposit->update(['success' => 0]);

            foreach($deposit->shifts as $shift) {
                if ($deposit->caregiver) {
                    $shift->statusManager()->ackReturnedCaregiverDeposit();
                }
                else if ($deposit->business) {
                    $shift->statusManager()->ackReturnedBusinessDeposit();
                }
            }
        }
    }
}
