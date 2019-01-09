<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Payer;

class ManagePayersTest extends TestCase
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
    public function an_office_user_can_get_a_list_of_all_payers()
    {
        factory('App\Billing\Payer', 5)->create(['chain_id' => $this->chain->id]);

        $this->assertCount(5, $this->chain->payers);

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
}
