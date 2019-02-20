<?php

namespace App\Listeners;

use App\Billing\Service;
use App\Events\Contracts\BusinessChainEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateDefaultService
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
    public function handle(BusinessChainEvent $event)
    {
        Service::create([
            'name' => Service::DEFAULT_SERVICE_NAME,
            'code' => '',
            'default' => true,
            'chain_id' => $event->getBusinessChain()->id,
        ]);
    }
}
