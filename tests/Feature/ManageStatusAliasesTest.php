<?php

namespace Tests\Feature;

use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\StatusAlias;
use App\BusinessChain;

class ManageStatusAliasesTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;
    
    public function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->assertEquals($this->chain->id, $this->officeUser->chain_id);
    }

    /** @test */
    public function an_office_user_can_create_a_new_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();

        $this->assertCount(0, $this->chain->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    public function office_users_cannot_create_status_aliases_for_chains_they_do_not_belong_to()
    {
        $this->actingAs($this->officeUser->user);

        $otherChain = factory(BusinessChain::class)->create();

        $data = factory(StatusAlias::class)->raw(['chain_id' => $otherChain->id]);

        $this->assertCount(0, $this->chain->statusAliases);
        $this->assertCount(0, $otherChain->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->chain->fresh()->statusAliases);
        $this->assertCount(0, $otherChain->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_a_name()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['name']);

        $this->assertCount(0, $this->chain->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertCount(0, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    function creating_aliases_restricts_unique_names_per_chain()
    {
        $this->actingAs($this->officeUser->user);

        $alias = factory(StatusAlias::class)->create([
            'name' => 'Test',
            'chain_id' => $this->chain->id,
            'type' => 'client',
        ]);

        $data = factory(StatusAlias::class)->raw(['name' => $alias->name, 'type' => 'client']);
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
        $this->assertCount(1, $this->chain->fresh()->statusAliases);

        $otherChain = factory(BusinessChain::class)->create();
        $alias->update(['chain_id' => $otherChain->id]);
        $this->assertCount(0, $this->chain->fresh()->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);
        $this->assertCount(1, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    function updating_aliases_restricts_unique_names_per_chain()
    {
        $this->actingAs($this->officeUser->user);

        $alias1 = factory(StatusAlias::class)->create([
            'name' => 'Test',
            'chain_id' => $this->chain->id,
            'type' => 'client',
        ]);
        $alias2 = factory(StatusAlias::class)->create([
            'name' => 'Anything Else',
            'chain_id' => $this->chain->id,
            'type' => 'client',
        ]);

        $alias2->name = 'Test';
        $this->patchJson(route('business.status-aliases.update', ['status_alias' => $alias2]), $alias2->toArray())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
        $this->assertEquals('Anything Else', $alias2->fresh()->name);

        $otherChain = factory(BusinessChain::class)->create();
        $alias1->update(['chain_id' => $otherChain->id]);
        $this->patchJson(route('business.status-aliases.update', ['status_alias' => $alias2]), $alias2->toArray())
            ->assertStatus(200);
        $this->assertEquals('Test', $alias2->fresh()->name);
    }

    /** @test */
    function creating_aliases_restricts_unique_names_per_alias_type()
    {
        $this->actingAs($this->officeUser->user);

        $alias = factory(StatusAlias::class)->create([
            'name' => 'Test',
            'chain_id' => $this->chain->id,
            'type' => 'client',
        ]);

        $data = factory(StatusAlias::class)->raw(['name' => $alias->name, 'type' => 'client']);
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
        $this->assertCount(1, $this->chain->fresh()->statusAliases);

        $alias->update(['type' => 'caregiver']);
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);
        $this->assertCount(2, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_a_valid_type()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['type']);

        $this->assertCount(0, $this->chain->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        $this->assertCount(0, $this->chain->fresh()->statusAliases);

        $data['type'] = 'pizza';
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        $this->assertCount(0, $this->chain->fresh()->statusAliases);

        $data['type'] = 'caregiver';
        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    public function creating_a_new_alias_requires_an_active_setting()
    {
        $this->actingAs($this->officeUser->user);

        $data = factory(StatusAlias::class)->raw();
        unset($data['active']);

        $this->assertCount(0, $this->chain->statusAliases);

        $this->postJson(route('business.status-aliases.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['active']);

        $this->assertCount(0, $this->chain->statusAliases);
    }

    /** @test */
    public function an_office_user_can_update_a_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['name' => 'Test', 'active' => '0']);

        $this->assertEquals('Test', $status->name);
        $this->assertEquals(0, $status->active);

        $data = array_merge($status->toArray(), [
            'name' => 'Updated',
            'active' => 1,
        ]);

        $this->patchJson(route('business.status-aliases.update', ['status_alias' => $status]), $data)
            ->assertStatus(200);

        $this->assertEquals(1, $status->fresh()->active);
        $this->assertEquals('Updated', $status->fresh()->name);
    }

    /** @test */
    public function an_office_user_can_delete_a_status_alias()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create();

        $this->assertCount(1, $this->chain->statusAliases);

        $this->deleteJson(route('business.status-aliases.destroy', ['status_alias' => $status]))
            ->assertStatus(200);

        $this->assertCount(0, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    public function deleting_a_status_alias_will_fail_if_it_is_in_use()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create();
        $this->caregiver->update(['status_alias_id' => $status->id]);

        $this->assertEquals($status->id, $this->caregiver->fresh()->status_alias_id);

        $this->assertCount(1, $this->chain->statusAliases);

        $this->deleteJson(route('business.status-aliases.destroy', ['status_alias' => $status]))
            ->assertStatus(403);

        $this->assertCount(1, $this->chain->fresh()->statusAliases);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_the_available_status_aliases()
    {
        $this->disableExceptionHandling();
        
        factory(StatusAlias::class, 5)->create(['type' => 'caregiver']);
        factory(StatusAlias::class, 3)->create(['type' => 'client']);
        
        $this->actingAs($this->officeUser->user);

        $this->getJson(route('business.status-aliases.index') . '?business_id='.$this->business->id)
            ->assertStatus(200)
            ->assertJsonStructure(['caregiver', 'client'])
            ->assertJsonCount(5, 'caregiver')
            ->assertJsonCount(3, 'client');
    }

    /** @test */
    public function changing_a_statuses_active_attribute_will_fail_if_it_is_in_use()
    {
        $this->actingAs($this->officeUser->user);

        $status = factory(StatusAlias::class)->create(['active' => 1]);
        $this->caregiver->update(['status_alias_id' => $status->id]);

        $this->assertEquals($status->id, $this->caregiver->fresh()->status_alias_id);

        $data = $status->toArray();
        $data['active'] = 0;
        $this->assertEquals(1, $status->fresh()->active);

        $this->patchJson(route('business.status-aliases.update', ['status_alias' => $status]), $data)
            ->assertStatus(403);

        $this->assertEquals(1, $status->fresh()->active);
    }
}
