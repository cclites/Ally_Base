<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\StatusAlias;
use App\Business;
use App\BusinessChain;
use App\Client;

class ManageUserStatusTest extends TestCase
{
    use RefreshDatabase;
    
    public $client;
    public $business;
    public $officeUser;
    public $chain;
    
    public function setUp()
    {
        parent::setUp();
    
        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->chain = $this->business->businessChain;
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
        $this->business->users()->attach($this->officeUser->id);
    
        $this->caregiver = factory('App\Caregiver')->create();
        $this->client->caregivers()->save($this->caregiver);
        $this->business->chain->assignCaregiver($this->caregiver);

        factory(StatusAlias::class)->create(['type' => 'client', 'name' => 'Working', 'active' => true]);
        factory(StatusAlias::class)->create(['type' => 'client', 'name' => 'Discharged', 'active' => false]);
        factory(StatusAlias::class)->create(['type' => 'client', 'name' => 'Hold', 'active' => false]);
        factory(StatusAlias::class)->create(['type' => 'client', 'name' => 'Pending', 'active' => false]);
        factory(StatusAlias::class)->create(['type' => 'client', 'name' => 'Non-Admin', 'active' => false]);
        factory(StatusAlias::class)->create(['type' => 'caregiver', 'name' => 'Working', 'active' => true]);
        factory(StatusAlias::class)->create(['type' => 'caregiver', 'name' => 'Hold', 'active' => false]);
    }

    /** @test */
    public function an_office_user_can_change_a_caregivers_status_alias()
    {
        $this->disableExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['type' => 'caregiver']);

        $data = $this->caregiver->toArray();
        $data['status_alias_id'] = $status->id;

        $this->patchJson(route('business.caregivers.update', ['caregiver' => $this->caregiver]), $data)
            ->assertStatus(200);

        $this->assertEquals($status->id, $this->caregiver->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_only_change_a_caregivers_status_to_the_owning_businesses_statuses()
    {
        $this->actingAs($this->officeUser->user);
        $otherChain = factory(BusinessChain::class)->create();

        $status = factory(StatusAlias::class)->create(['type' => 'caregiver', 'chain_id' => $otherChain->id]);

        $data = $this->caregiver->toArray();
        $data['status_alias_id'] = $status->id;

        $this->patchJson(route('business.caregivers.update', ['caregiver' => $this->caregiver]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_alias_id']);

        $this->assertEquals(null, $this->caregiver->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_only_change_a_caregivers_status_to_caregiver_status_types()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['type' => 'client']);

        $data = $this->caregiver->toArray();
        $data['status_alias_id'] = $status->id;

        $this->patchJson(route('business.caregivers.update', ['caregiver' => $this->caregiver]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_alias_id']);

        $this->assertEquals(null, $this->caregiver->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_change_a_clients_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['type' => 'client']);

        $data = $this->client->toArray();
        $data['ssn'] = '123-23-1234';
        $data['status_alias_id'] = $status->id;
        // onboard status must be set for update to succeed
        $data['agreement_status'] = Client::NEEDS_AGREEMENT;

        $this->patchJson(route('business.clients.update', ['client' => $this->client]), $data)
            ->assertStatus(200);

        $this->assertEquals($status->id, $this->client->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_only_change_a_clients_status_to_the_owning_businesses_statuses()
    {
        $this->actingAs($this->officeUser->user);
        $otherChain = factory(BusinessChain::class)->create();

        $status = factory(StatusAlias::class)->create(['type' => 'client', 'chain_id' => $otherChain->id]);

        $data = $this->client->toArray();
        $data['status_alias_id'] = $status->id;

        $this->patchJson(route('business.clients.update', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_alias_id']);

        $this->assertEquals(null, $this->client->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_only_change_a_clients_status_to_client_status_types()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['type' => 'caregiver']);

        $data = $this->client->toArray();
        $data['status_alias_id'] = $status->id;

        $this->patchJson(route('business.clients.update', ['client' => $this->client]), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_alias_id']);

        $this->assertEquals(null, $this->client->fresh()->status_alias_id);
    }

    /** @test */
    public function an_office_user_can_filter_caregivers_by_their_status_aliases()
    {
        $this->actingAs($this->officeUser->user);

        $caregiver2 = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($caregiver2);

        $caregiver3 = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($caregiver3);

        $status = factory(StatusAlias::class)->create(['active' => true, 'type' => 'caregiver']);
        $status2 = factory(StatusAlias::class)->create(['active' => true, 'type' => 'caregiver']);
        $status3 = factory(StatusAlias::class)->create(['active' => false, 'type' => 'caregiver']);

        $this->caregiver->update(['status_alias_id' => $status->id, 'active' => 1]);
        $caregiver2->update(['status_alias_id' => $status2->id, 'active' => 1]);
        $caregiver3->update(['status_alias_id' => $status3->id, 'active' => 0]);

        $this->getJson(route('business.caregivers.index')."?active=1&status={$status->id}")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $this->caregiver->id]);

        $this->getJson(route('business.caregivers.index')."?active=1&status={$status2->id}")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $caregiver2->id]);

        $this->getJson(route('business.caregivers.index')."?active=0&status={$status3->id}")
        ->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $caregiver3->id]);
    }

    /** @test */
    public function an_office_user_can_filter_clients_by_their_status_aliases()
    {
        $this->actingAs($this->officeUser->user);

        $client2 = factory('App\Client')->create();
        $client3 = factory('App\Client')->create();

        $status = factory(StatusAlias::class)->create(['active' => true, 'type' => 'client']);
        $status2 = factory(StatusAlias::class)->create(['active' => true, 'type' => 'client']);
        $status3 = factory(StatusAlias::class)->create(['active' => false, 'type' => 'client']);

        $this->client->update(['status_alias_id' => $status->id, 'active' => 1]);
        $client2->update(['status_alias_id' => $status2->id, 'active' => 1]);
        $client3->update(['status_alias_id' => $status3->id, 'active' => 0]);

        // because now the result returns a nested structure, the assertJsonCount had to be nested as well
        $this->getJson(route('business.clients.index')."?active=1&status={$status->id}")
            ->assertStatus(200)
            ->assertJsonCount( 1, 'clients' )
            ->assertJsonFragment(['id' => $this->client->id]);

            $this->getJson(route('business.clients.index')."?active=1&status={$status2->id}")
            ->assertStatus(200)
            ->assertJsonCount( 1, 'clients' )
            ->assertJsonFragment(['id' => $client2->id]);

        $tits = $this->getJson(route('business.clients.index')."?active=0&status={$status3->id}")
            ->assertStatus(200)
            ->assertJsonCount( 1, 'clients' )
            ->assertJsonFragment(['id' => $client3->id]);
    }
}
