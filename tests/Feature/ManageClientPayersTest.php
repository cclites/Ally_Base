<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Payer;
use App\Billing\ClientPayer;

class ManageClientPayersTest extends TestCase
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
        $this->business->caregivers()->save($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->chain->id]);
        $this->business->users()->attach($this->officeUser);

        $this->payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function an_office_user_can_add_a_client_payer()
    {
        $payer = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id]);

        $this->assertCount(0, $this->client->payers);

        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $payer->toArray())
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function an_office_user_can_update_a_client_payer()
    {
        $payer = factory('App\Billing\ClientPayer')->create(['client_id' => $this->client->id]);

        $payer->policy_number = '1234';
        $this->patchJson(route('business.clients.payers.update', ['client' => $this->client, 'clientPayer' => $payer]), $payer->toArray())
            ->assertStatus(200);

        $this->assertEquals('1234', $payer->fresh()->policy_number);
    }

    /** @test */
    public function an_office_user_can_remove_a_client_payer()
    {
        $payer = factory('App\Billing\ClientPayer')->create(['client_id' => $this->client->id]);

        $this->assertCount(1, $this->client->payers);

        $this->deleteJson(route('business.clients.payers.destroy', ['client' => $this->client, 'clientPayer' => $payer]))
            ->assertStatus(200);

        $this->assertCount(0, $this->client->fresh()->payers);
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
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422);

        $data['payer_id'] = $otherPayer->id;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422);
    
        $this->assertCount(0, $this->client->fresh()->payers);
    }
    
    /** @test */
    public function client_payers_can_have_a_blank_payer_id_to_represent_the_client()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payer_id'] = null;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function new_client_payers_cannot_contain_duplicate_payer_ids()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payer_id'] = $this->payer->id;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);

        // must change something else about the data or server returns 409 conflict
        $data['policy_number'] = 'new';
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422);
    
        // test again with null value because it requires seperate validation
        $data['payer_id'] = null;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(200);
        $this->assertCount(2, $this->client->fresh()->payers);

        $data['policy_number'] = 'new again';
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422);
        $this->assertCount(2, $this->client->fresh()->payers);
    }

    /** @test */
    public function new_client_payers_should_be_added_to_the_bottom_priority()
    {
        $this->withExceptionHandling();

        factory('App\Billing\ClientPayer', 5)->create(['client_id' => $this->client->id]);
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id, 'payer_id' => $payer])->toArray();

        $this->assertCount(5, $this->client->payers);

        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertJsonFragment(['priority' => 6])
            ->assertStatus(200);

        $this->assertCount(6, $this->client->fresh()->payers);
    }

    /** @test */
    public function an_office_user_can_update_the_client_payer_priority()
    {
        factory('App\Billing\ClientPayer', 5)->create(['client_id' => $this->client->id]);
        $chainPayer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $payer = factory('App\Billing\ClientPayer')->create(['client_id' => $this->client->id, 'payer_id' => $chainPayer]);

        $this->assertCount(6, $this->client->payers);
        $this->assertEquals($payer->priority, 6);

        $this->patchJson(route('business.clients.payers.priority', ['client' => $this->client, 'payer' => $payer]), [
            'priority' => 2,
        ])->assertStatus(200)
            ->assertJsonFragment(['priority' => 2]);

        $this->assertEquals($payer->fresh()->priority, 2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $this->client->fresh()->payers->pluck('priority')->toArray());
    }

    /** @test */
    public function when_a_client_payer_is_removed_it_should_shift_all_other_priorities_up()
    {
        factory('App\Billing\ClientPayer', 5)->create(['client_id' => $this->client->id]);
        $chainPayer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $payer = $this->client->payers->where('priority', 3)->first();

        $this->assertCount(5, $this->client->payers);
        $this->assertEquals($payer->priority, 3);

        $this->deleteJson(route('business.clients.payers.destroy', ['client' => $this->client, 'clientPayer' => $payer]))
            ->assertStatus(200);

        $this->assertCount(4, $this->client->fresh()->payers);
        $this->assertEquals([1, 2, 3, 4], $this->client->fresh()->payers->pluck('priority')->toArray());
    }

    /** @test */
    public function client_payers_must_have_a_valid_payment_allocation_method()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payment_allocation'] = 'invalid';
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422);

        $this->assertCount(0, $this->client->fresh()->payers);
    }

    /** @test */
    public function if_a_client_payers_allocation_method_is_balance_then_split_and_allowance_are_not_required()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payment_allocation'] = ClientPayer::ALLOCATION_BALANCE;
        $data['payment_allowance'] = null;
        $data['payment_split'] = null;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->payers);
    }

    /** @test */
    public function if_a_client_payers_allocation_method_is_daily_weekly_or_monthly_then_allowance_is_required()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payment_allocation'] = ClientPayer::ALLOCATION_DAILY;
        $data['payment_allowance'] = null;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_allowance');

        $data['payment_allocation'] = ClientPayer::ALLOCATION_WEEKLY;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_allowance');

        $data['payment_allocation'] = ClientPayer::ALLOCATION_MONTHLY;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_allowance');
            
        $this->assertCount(0, $this->client->fresh()->payers);
    }

    /** @test */
    public function if_a_client_payers_allocation_method_is_split_then_split_field_is_required()
    {
        $this->withExceptionHandling();

        $data = factory('App\Billing\ClientPayer')->make(['client_id' => $this->client->id])->toArray();

        $this->assertCount(0, $this->client->payers);

        $data['payment_allocation'] = ClientPayer::ALLOCATION_SPLIT;
        $data['split_percentage'] = null;
        $this->postJson(route('business.clients.payers.store', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('split_percentage');

        $this->assertCount(0, $this->client->fresh()->payers);
    }
}
