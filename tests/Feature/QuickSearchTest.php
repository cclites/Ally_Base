<?php

namespace Tests\Feature;

use App\BusinessChain;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuickSearchTest extends TestCase
{
    use RefreshDatabase;

    public $client;
    public $caregiver;
    public $business;
    public $admin;
    public $officeUser;
    
    public function setUp() : void
    {
        parent::setUp();

        $this->admin = factory('App\Admin')->create();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->chain->assignCaregiver($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /** @test */
    public function only_admins_can_office_users_can_see_quick_search()
    {
        $this->actingAs($this->admin->user);
        $this->followingRedirects()
            ->get(route('home'))
            ->assertStatus(200)
            ->assertSee('<quick-search>');

        $this->actingAs($this->officeUser->user);
        $this->followingRedirects()
            ->get(route('home'))
            ->assertStatus(200)
            ->assertSee('<quick-search>');

        $this->actingAs($this->caregiver->user);
        $this->followingRedirects()
            ->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee('<quick-search>');

        $this->actingAs($this->client->user);
        $this->followingRedirects()
            ->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee('<quick-search>');
    }

    /** @test */
    public function an_admin_can_search_all_users()
    {
        $this->withoutExceptionHandling();
        
        $this->actingAs($this->admin->user);

        $anotherBusiness = factory(\App\Business::class)->create();
        $anotherCG = factory('App\Caregiver')->create();
        $anotherBusiness->assignCaregiver($anotherCG);
        factory('App\Client', 5)->create([
            'business_id' => $anotherBusiness->id,
        ]);
        
        $filter = '';

        $result = $this->get(route('business.quick-search') . "?q=$filter")
            ->assertStatus(200)
            ->assertJsonCount(8, 'data');
    }

    /** @test */
    public function an_office_user_can_only_search_their_own_data()
    {
        $this->actingAs($this->officeUser->user);

        $anotherChain = factory(BusinessChain::class)->create();
        $anotherBusiness = factory(\App\Business::class)->create(['chain_id' => $anotherChain->id]);
        $anotherCG = factory('App\Caregiver')->create();
        $anotherBusiness->assignCaregiver($anotherCG);
        factory('App\Client', 5)->create([
            'business_id' => $anotherBusiness->id,
        ]);
        
        $filter = '';

        $result = $this->get(route('business.quick-search') . "?q=$filter")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function quick_search_should_search_names()
    {
        $this->actingAs($this->admin->user);

        factory('App\Client', 10)->create();
        factory('App\Client', 1)->create([
            'firstname' => 'xxxxxxxxxxxxx',
            'lastname' => 'smith',
        ]);
        
        $filter = 'xxxx smith';

        $result = $this->get(route('business.quick-search') . "?q=$filter")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

}
