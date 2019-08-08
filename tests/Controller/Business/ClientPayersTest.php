<?php

namespace Tests\Controller\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Payer;
use App\Billing\ClientPayer;
use Illuminate\Support\Carbon;
use App\Billing\ClientInvoice;

class ClientPayersTest extends TestCase
{
    use RefreshDatabase;

    public $officeUser;
    public $client;
    public $caregiver;
    public $business;
    public $chain;
    public $payer;
    
    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->business = factory('App\Business')->create();
        $this->chain = $this->business->chain;
        
        $this->client = factory('App\Client')->create(['business_id' => $this->business->id]);

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->chain->id]);
        $this->business->users()->attach($this->officeUser);

        $this->payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->actingAs($this->officeUser->user);
    }

    /**
     * @test
     * 
     * testing the output of the Claims & AR report
     * 
     * not necessarily runnning many assertions on it, I just like having a CLI method of viewing the results.
     * Plus setting up the factories helps me understand what data & relationships that the report works with
     */
    public function the_claims_and_ar_report_works_soundly()
    {

        factory( ClientInvoice::class, 50 )->create();

        $query_string = '?json=1&businesses=3&start_date=07/09/2019&end_date=08/08/2019&invoiceType=&client_id=&payer_id=';
        $data = $this->get( route( 'business.claims-ar' ) . $query_string )
            ->assertSuccessful();

        dd( $data );
    }

    /** @test */
    public function a_user_can_update_the_client_payers()
    {
        $this->withoutExceptionHandling();

        $payers = factory('App\Billing\ClientPayer', 1)->make(['client_id' => $this->client->id]);

        $this->assertCount(0, $this->client->payers);

        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => $payers])
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function client_payers_priority_should_be_sorted_as_shown_on_save()
    {
        $payer1 = factory('App\Billing\ClientPayer')->create(['priority' => 1, 'client_id' => $this->client->id, 'effective_start' => '2019-01-02', 'effective_end' => '2019-01-03']);
        $payer2 = factory('App\Billing\ClientPayer')->create(['priority' => 2, 'client_id' => $this->client->id, 'effective_start' => '2019-01-04', 'effective_end' => '2019-01-05']);
        $payer3 = factory('App\Billing\ClientPayer')->create(['priority' => 3, 'client_id' => $this->client->id, 'effective_start' => '2019-01-06', 'effective_end' => '9999-12-31']);

        $this->assertCount(3, $this->client->payers);

        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$payer1, $payer3, $payer2]])
            ->assertStatus(200);

        $this->assertCount(3, $this->client->fresh()->payers);
        $this->assertEquals(1, $payer1->fresh()->priority);
        $this->assertEquals(2, $payer3->fresh()->priority);
        $this->assertEquals(3, $payer2->fresh()->priority);
    }

    /** @test */
    public function client_payers_require_valid_dates()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\ClientPayer')->create(['client_id' => $this->client->id]);

        $this->assertCount(1, $this->client->payers);

        $data = $payer->toArray();
        $data['effective_start'] = 'sdgfhghg';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(422);

        $this->assertCount(1, $this->client->fresh()->payers);

        $data['effective_end'] = 'invalid';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(422);

        $this->assertCount(1, $this->client->fresh()->payers);

        $data['effective_start'] = '01/01/2017';
        $data['effective_end'] = '12/31/9999';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(200);
                
        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function an_office_user_can_update_a_client_payer()
    {
        $payer = factory('App\Billing\ClientPayer')->create(['client_id' => $this->client->id]);

        $payer->policy_number = '1234';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$payer->toArray()]])
            ->assertStatus(200);

        $this->assertEquals('1234', $payer->fresh()->policy_number);
    }

    /** @test */
    public function an_office_user_can_remove_a_client_payer()
    {
        $payer = factory('App\Billing\ClientPayer', 2)->create(['client_id' => $this->client->id]);

        $this->assertCount(2, $this->client->payers);

        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$this->client->payers->first()]])
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function client_payers_must_contain_a_valid_payer_id_from_the_owning_business_chain()
    {
        $this->withExceptionHandling();

        $otherChain = factory('App\BusinessChain')->create();
        $otherPayer = factory('App\Billing\Payer')->create(['chain_id' => $otherChain]);
        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payer_id'] = 12345;
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(422);

        $data['payer_id'] = $otherPayer->id;
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(422);

        $this->assertCount(0, $this->client->fresh()->payers);
    }
    
    /** @test */
    public function client_payers_can_have_a_payer_id_of_zero_to_represent_the_client()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payer_id'] = 0;
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function adding_client_payers_should_fail_if_effective_dates_overlap_for_the_same_payer_id()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payer_id'] = $this->payer->id;
        $data['effective_start'] = '2018-01-01';
        $data['effective_end'] = '9999-12-31';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);

        $data['effective_start'] = '2018-12-31'; // overlaps by 1 day
        $data['effective_end'] = '2019-12-31';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client]), ['payers' => [$data]])
            ->assertStatus(422);

        $this->assertCount(1, $this->client->fresh()->payers);
    }
}
