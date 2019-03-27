<?php

namespace App\Listeners;

use App\Billing\ClientPayer;
use App\Billing\Payer;
use App\Events\Contracts\ClientEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateDefaultClientPayer
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
     * @param  object  $event
     * @return void
     */
    public function handle(ClientEvent $event)
    {
        ClientPayer::create([
            'client_id' => $event->getClient()->id,
            'payer_id' => Payer::PRIVATE_PAY_ID,
            'effective_start' => date('Y-m-d', strtotime('-45 days')),
            'effective_end' => '9999-12-31',
            'payment_allocation' => ClientPayer::ALLOCATION_BALANCE,
        ]);
    }
}
