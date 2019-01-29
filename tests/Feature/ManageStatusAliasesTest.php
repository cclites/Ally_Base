<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\StatusAlias;
use App\Business;

class ManageStatusAliasesTest extends TestCase
{
    use RefreshDatabase;
    
    public $client;
    public $business;
    public $officeUser;
    public $contact;
    
    public function setUp()
    {
        parent::setUp();
    
        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
        $this->officeUser->businesses()->attach($this->business->id);
    
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->caregivers()->save($this->caregiver);
        $this->client->caregivers()->save($this->caregiver);
    }

    /** @test */
    public function an_office_user_can_create_a_new_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();

        $this->assertCount(0, $this->business->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->business->fresh()->statusAliases);
    }

    /** @test */
    public function office_users_cannot_create_status_aliases_for_businesses_they_do_not_belong_to()
    {
        $this->actingAs($this->officeUser->user);

        $otherBusiness = factory(Business::class)->create();

        $data = factory(StatusAlias::class)->raw(['business_id' => $otherBusiness->id]);

        $this->assertCount(0, $this->business->statusAliases);
        $this->assertCount(0, $otherBusiness->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(403);

        $this->assertCount(0, $this->business->fresh()->statusAliases);
        $this->assertCount(0, $otherBusiness->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_a_name()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['name']);

        $this->assertCount(0, $this->business->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertCount(0, $this->business->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_a_valid_type()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['type']);

        $this->assertCount(0, $this->business->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        $this->assertCount(0, $this->business->fresh()->statusAliases);

        $data['type'] = 'pizza';
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        $this->assertCount(0, $this->business->fresh()->statusAliases);

        $data['type'] = 'caregiver';
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->business->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_an_active_setting()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['active']);

        $this->assertCount(0, $this->business->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['active']);

        $this->assertCount(0, $this->business->statusAliases);
    }

    /** @test */
    public function an_office_user_can_update_a_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['name' => 'Test', 'active' => '0']);

        $this->assertEquals('Test', $status->name);
        $this->assertEquals(false, $status->active);

        $data = array_merge($status->toArray(), [
            'name' => 'Updated',
            'active' => 1,
        ]);

        $this->patchJson(route('business.status-aliases.update', ['status_alias' => $status]), $data)
            ->assertStatus(200);

        $this->assertEquals(true, $status->fresh()->active);
        $this->assertEquals('Updated', $status->fresh()->name);
    }

    /** @test */
    public function an_office_user_can_delete_a_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create();

        $this->assertCount(1, $this->business->statusAliases);

        $this->deleteJson(route('business.status-aliases.destroy', ['status_alias' => $status]))
            ->assertStatus(200);

        $this->assertCount(0, $this->business->fresh()->statusAliases);
    }

    /** @test */
    public function deleting_a_status_alias_will_fail_if_it_is_in_use()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create();
        $this->caregiver->update(['status_alias_id' => $status->id]);

        $this->assertEquals($status->id, $this->caregiver->fresh()->status_alias_id);

        $this->assertCount(1, $this->business->statusAliases);

        $this->deleteJson(route('business.status-aliases.destroy', ['status_alias' => $status]))
            ->assertStatus(403);

        $this->assertCount(1, $this->business->fresh()->statusAliases);
    }
}
