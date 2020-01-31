<?php

namespace Tests\Feature;

use App\Billing\ClientPayer;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManagePayersTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->createBusinessWithUsers(false);

        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_all_payers()
    {
        factory('App\Billing\Payer', 5)->create(['chain_id' => $this->chain->id]);

        $this->assertCount(5, $this->chain->fresh()->payers);

        $this->getJson(route('business.payers.index') . '?json=1')
            ->assertStatus(200)
            ->assertJsonCount(5);
    }

    /** @test */
    public function office_users_can_only_see_all_payers_on_their_business_chain()
    {
        $otherChain = factory('App\BusinessChain')->create();
        factory('App\Billing\Payer', 5)->create(['chain_id' => $this->chain->id]);
        factory('App\Billing\Payer', 5)->create(['chain_id' => $otherChain]);

        $this->assertCount(5, $this->chain->payers);

        $this->getJson(route('business.payers.index') . '?json=1')
            ->assertStatus(200)
            ->assertJsonCount(5);
    }
    
    /** @test */
    public function an_office_user_can_create_a_new_payer()
    {
        $payer = factory('App\Billing\Payer')->make();

        $this->assertCount(0, $this->chain->payers);

        $this->postJson(route('business.payers.store'), $payer->toArray())
            ->assertStatus(200);

        $this->assertCount(1, $this->chain->fresh()->payers);
    }

    /** @test */
    public function a_payer_requires_a_name()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->make();
        $payer->name = null;

        $this->assertCount(0, $this->chain->payers);

        $this->postJson(route('business.payers.store'), $payer->toArray())
            ->assertStatus(422);

        $this->assertCount(0, $this->chain->fresh()->payers);
    }

    /** @test */
    public function an_office_user_can_update_a_payer()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->assertCount(1, $this->chain->payers);

        $payer->name = 'New Name';

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $payer->toArray())
            ->assertStatus(200)
            ->assertSee('New Name');

        $this->assertCount(1, $this->chain->fresh()->payers);

        $this->assertEquals($payer->fresh()->name, 'New Name');
    }
    
    /** @test */
    public function an_office_user_cannot_update_another_chains_payer()
    {
        $this->withExceptionHandling();

        $otherChain = factory('App\BusinessChain')->create();
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $otherChain->id]);
        $this->assertCount(0, $this->chain->payers);

        $payer->name = 'New Name';

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $payer->toArray())
            ->assertStatus(403);

        $this->assertNotEquals($payer->fresh()->name, 'New Name');
    }

    /** @test */
    public function an_office_user_can_delete_a_payer()
    {
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->assertCount(1, $this->chain->payers);

        $this->deleteJson(route('business.payers.destroy', ['payer' => $payer]))
            ->assertStatus(200);

        $this->assertCount(0, $this->chain->fresh()->payers);
    }
    
    /** @test */
    public function an_office_user_cannot_delete_another_chains_payer()
    {
        $this->withExceptionHandling();

        $otherChain = factory('App\BusinessChain')->create();
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $otherChain->id]);
        $this->assertCount(1, $otherChain->payers);

        $this->deleteJson(route('business.payers.destroy', ['payer' => $payer]))
            ->assertStatus(403);

        $this->assertCount(1, $otherChain->fresh()->payers);
    }

    /** @test */
    public function an_office_user_cannot_delete_payers_that_are_assigned()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->assertCount(1, $this->chain->payers);

        factory(ClientPayer::class)->create([
            'payer_id' => $payer->id,
            'client_id' => $this->client,
        ]);
        $this->assertCount(1, $this->client->fresh()->payers);

        $this->deleteJson(route('business.payers.destroy', ['payer' => $payer]))
            ->assertStatus(500)
            ->assertSee('because it is currently assigned');

        $this->assertCount(1, $this->chain->fresh()->payers);
    }

    /** @test */
    public function an_office_user_can_create_a_payer_with_rates()
    {
        $payer = factory('App\Billing\Payer')->make(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $this->assertCount(0, $this->chain->payers);

        $this->postJson(route('business.payers.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->chain->fresh()->payers);

        $this->assertCount(1, $this->chain->fresh()->payers->first()->rates);
    }

    /** @test */
    public function an_office_user_can_update_the_payer_rates()
    {
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $this->assertCount(0, $payer->rates);

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $payer->fresh()->rates);
    }

    /** @test */
    public function missing_payer_rates_should_automatically_remove_on_update()
    {
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->create(['payer_id' => $payer->id]);
        $otherRate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $this->assertCount(1, $payer->rates);

        $data = array_merge($payer->toArray(), [
            'rates' => [$otherRate->toArray()],
        ]);

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $payer->fresh()->rates);
        $this->assertNotEquals($rate->id, $payer->fresh()->rates->first()->id);
    }

    /** @test */
    public function existing_payer_rates_should_auto_update()
    {
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->create(['payer_id' => $payer->id]);

        $this->assertCount(1, $payer->rates);

        $rate->hourly_rate = 55;

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $payer->fresh()->rates);
        $this->assertEquals($rate->id, $payer->fresh()->rates->first()->id);
    }

    /** @test */
    public function payer_rates_can_be_created_without_a_service_id()
    {
        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $this->assertCount(0, $payer->rates);

        $rate->service_id = null;
        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $payer->fresh()->rates);
        $this->assertNull($payer->fresh()->rates->first()->service_id);
    }

    /** @test */
    public function payer_rates_must_have_valid_hourly_rates()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $this->assertCount(0, $payer->rates);

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $data['rates'][0]['hourly_rate'] = -25;
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $data['rates'][0]['hourly_rate'] = 99999;
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $data['rates'][0]['fixed_rate'] = -25;
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $data['rates'][0]['fixed_rate'] = 99999;
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);
    
        $this->assertCount(0, $payer->fresh()->rates);
    }

    /** @test */
    public function payer_rates_must_have_valid_dates()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);

        $this->assertCount(0, $payer->rates);

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $data['rates'][0]['effective_start'] = '2019-3301-01';
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $data['rates'][0]['effective_end'] = 'invalid';
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $this->assertCount(0, $payer->fresh()->rates);

        $data['rates'][0]['effective_start'] = '01/01/2017';
        $data['rates'][0]['effective_end'] = '12/31/9999';
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(200);
    
        $this->assertCount(1, $payer->fresh()->rates);
    }

    /** @test */
    public function payer_rates_must_have_a_valid_service_id()
    {
        $this->withExceptionHandling();

        $payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $rate = factory('App\Billing\PayerRate')->make(['payer_id' => $payer->id]);
        $otherChain = factory('App\BusinessChain')->create();
        $otherService = factory('App\Billing\Service')->create(['chain_id' => $otherChain->id]);

        $data = array_merge($payer->toArray(), [
            'rates' => [$rate->toArray()],
        ]);

        $data['rates'][0]['service_id'] = 2345355467; // fake id number
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $data['rates'][0]['service_id'] = $otherService->id; 
        $this->patchJson(route('business.payers.update', ['payer' => $payer]), $data)
            ->assertStatus(422);

        $this->assertCount(0, $payer->fresh()->rates);
    }
}
