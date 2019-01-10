<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Payer;

class ManageClientRatesTest extends TestCase
{
    use RefreshDatabase;

    public $officeUser;
    public $client;
    public $caregiver;
    public $business;
    public $chain;
    
    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->business = factory('App\Business')->create();
        $this->chain = $this->business->chain;
        
        $this->client = factory('App\Client')->create(['business_id' => $this->business->id]);

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->caregivers()->save($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->chain->id]);
        $this->business->users()->attach($this->officeUser);

        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function first_test()
    {
        factory('App\Billing\Payer', 5)->create(['chain_id' => $this->chain->id]);

        $this->assertCount(5, $this->chain->payers);

        $this->getJson(route('business.payers.index') . '?json=1')
            ->assertStatus(200)
            ->assertJsonCount(5);
    }
}
