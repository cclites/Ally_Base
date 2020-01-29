<?php

namespace Tests\Controller\Business;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\ClientRate;

class ClientRatesTest extends TestCase
{
    use RefreshDatabase;

    public $officeUser;
    public $client;
    public $caregiver;
    public $business;
    public $chain;
    public $service;
    public $payer;
    
    public function setUp() : void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->business = factory('App\Business')->create();
        $this->chain = $this->business->chain;
        
        $this->client = factory('App\Client')->create(['business_id' => $this->business->id]);
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($this->caregiver);
        $this->caregiver->clients()->save($this->client);

        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->chain->id]);
        $this->business->users()->attach($this->officeUser);

        $this->service = factory('App\Billing\Service')->create(['chain_id' => $this->chain->id]);
        $this->payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        
        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function a_user_can_get_a_list_of_the_client_rates()
    {
        $this->createRates(5);

        $this->assertCount(5, $this->client->rates);

        $this->getJson(route('business.clients.rates.index', ['client' => $this->client]))
            ->assertStatus(200)
            ->assertJsonCount(5);
    }

    /** @test */
    public function a_user_can_update_the_clients_rates()
    {
        $rates = factory('App\Billing\ClientRate', 1)->make(['client_id' => $this->client->id]);

        $this->assertCount(0, $this->client->rates);

        $data = ['rates' => $rates];

        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->rates);
    }

    /** @test */
    public function client_rates_require_valid_dates()
    {
        $this->withExceptionHandling();

        $rate = $this->createRate();

        $this->assertCount(1, $this->client->rates);

        $data = $rate->toArray();
        $data['effective_start'] = 'sdgfhghg';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $this->assertCount(1, $this->client->fresh()->rates);

        $data['effective_end'] = 'invalid';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $this->assertCount(1, $this->client->fresh()->rates);

        $data['effective_start'] = '01/01/2017';
        $data['effective_end'] = '12/31/9999';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);
                
        $this->assertCount(1, $this->client->fresh()->rates);
    }

    /** @test */
    public function client_rates_require_valid_rate_values()
    {
        $this->withExceptionHandling();

        $rate = $this->createRate();

        $this->assertCount(1, $this->client->rates);

        $data = $rate->toArray();
        $data['caregiver_hourly_rate'] = 'nan';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['caregiver_fixed_rate'] = 'nan';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['client_hourly_rate'] = 'nan';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['client_fixed_rate'] = 'nan';
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);
    
        $data = $rate->toArray();
        $data['caregiver_hourly_rate'] = 20.00;
        $data['client_hourly_rate'] = 25.00;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);

        $data = $rate->toArray();
        $data['caregiver_fixed_rate'] = 20.00;
        $data['client_fixed_rate'] = 25.00;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);
    }

    /** @test */
    public function client_rates_require_a_valid_service()
    {
        $this->withExceptionHandling();

        $rate = $this->createRate();

        $otherChain = factory('App\BusinessChain')->create();
        $otherService = factory('App\Billing\Service')->create(['chain_id' => $otherChain->id]);

        $data = $rate->toArray();
        $data['service_id'] = 2938523;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['service_id'] = $otherService->id;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['service_id'] = $this->service->id;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);

        $data = $rate->toArray();
        $data['service_id'] = null;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);
    }

    /** @test */
    public function client_rates_require_a_valid_payer()
    {
        $this->withExceptionHandling();

        $rate = $this->createRate();

        $otherChain = factory('App\BusinessChain')->create();
        $otherPayer = factory('App\Billing\Payer')->create(['chain_id' => $otherChain->id]);

        $data = $rate->toArray();
        $data['payer_id'] = 2938523;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['payer_id'] = $otherPayer->id;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['payer_id'] = $this->payer->id;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);

        $data = $rate->toArray();
        $data['payer_id'] = null;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);
    }

    /** @test */
    public function client_rates_require_a_valid_caregiver()
    {
        $this->withExceptionHandling();

        $rate = $this->createRate();

        $data = $rate->toArray();
        $data['caregiver_id'] = 2938523;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(422);

        $data = $rate->toArray();
        $data['caregiver_id'] = $this->caregiver->id;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);

        $data = $rate->toArray();
        $data['caregiver_id'] = null;
        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$data]])
            ->assertStatus(200);

        $this->assertCount(1, $this->client->fresh()->rates);
    }

    /** @test */
    public function updating_client_rates_should_fail_if_effective_dates_overlap_for_the_same_client_service_payer_caregiver_combo()
    {
        $this->withExceptionHandling();

        $rate = factory('App\Billing\ClientRate')->make(['client_id' => $this->client->id]);

        $rate1 = $rate->toArray();
        $rate1['effective_start'] = '2018-01-01';
        $rate1['effective_end'] = '2018-12-31';

        $rate2 = $rate->toArray();
        $rate2['effective_start'] = '2018-12-31'; // overlaps by 1 day
        $rate2['effective_end'] = '2019-12-31';

        $this->patchJson(route('business.clients.rates.update', ['client' => $this->client]), ['rates' => [$rate1, $rate2]])
            ->assertStatus(422);;

        $this->assertCount(0, $this->client->fresh()->rates);
    }

    protected function createRate(array $data = []): ClientRate
    {
        return factory(ClientRate::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $this->payer->id,
            'caregiver_id' => $this->caregiver->id,
        ]);
    }

    protected function createRates(int $count, array $data = []): Collection
    {
        $collection = new Collection();
        for($i=0; $i<$count; $i++) {
            $collection->push($this->createRate($data));
        }
        return $collection;
    }
}
