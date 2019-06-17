<?php

namespace Tests;

use App\Billing\ClientRate;

trait CreatesBusinesses
{
    /**
     * @var \App\Business
     */
    public $business;

    /**
     * @var \App\Client
     */
    public $client;

    /**
     * @var \App\Caregiver
     */
    public $caregiver;
    
    /**
     * @var \App\OfficeUser
     */
    public $officeUser;

    /**
     * @var \App\BusinessChain
     */
    public $chain;

    /**
     * Set up a business with an assigned office user that
     * has one client with an assigned caregiver.
     *
     * @param bool $includeClientRate
     * @return void
     */
    public function createBusinessWithUsers($includeClientRate = true)
    {
        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->chain = $this->business->chain;

        $this->caregiver = factory('App\Caregiver')->create();
        $this->caregiver->clients()->save($this->client);
        $this->chain->assignCaregiver($this->caregiver);

        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
        $this->officeUser->businesses()->attach($this->business->id);

        if ($includeClientRate) {
            factory(ClientRate::class)->create([
                'caregiver_id' => $this->caregiver->id,
                'client_id' => $this->client->id,
            ]);
        }
    }
}
