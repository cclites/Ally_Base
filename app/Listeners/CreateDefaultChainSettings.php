<?php

namespace App\Listeners;

use App\Events\Contracts\BusinessChainEvent;
use App\ChainClientTypeSettings;

class CreateDefaultChainSettings
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
     * @param BusinessChainEvent $event
     * @return void
     */
    public function handle(BusinessChainEvent $event)
    {
        ChainClientTypeSettings::create([
            'business_chain_id' => $event->getBusinessChain()->id,
            'medicaid_1099_default' => 'no',
            'medicaid_1099_edit' => '0',
            'medicaid_1099_from' => null,
            'private_pay_1099_default' => 'no',
            'private_pay_1099_edit' => '0',
            'private_pay_1099_from' => null,
            'other_1099_default' => 'no',
            'other_1099_edit' => '0',
            'other_1099_from' => null,
        ]);
    }
}