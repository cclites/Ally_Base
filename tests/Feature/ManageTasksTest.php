<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use Carbon\Carbon;
use App\OfficeUser;

class ManageTasksTest extends TestCase
{
    use RefreshDatabase;

    public $business;
    public $officeUser;
    
    public function setUp()
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /** @test */
    public function an_office_user_can_create_a_task()
    {
        $this->actingAs($this->officeUser->user);

        $this->assertCount(0, \App\Task::all());

        $data = [
            'name' => 'test task',
            'notes' => 'notes',
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, \App\Task::all());
    }

    /** @test */
    public function a_task_must_have_a_name()
    {
        $this->actingAs($this->officeUser->user);

        $data = [
            'notes' => 'notes',
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertCount(0, \App\Task::all());
    }

    /** @test */
    public function a_task_can_have_notes()
    {
        $this->actingAs($this->officeUser->user);

        $data = [
            'name' => 'test task',
            'notes' => 'test notes',
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $this->assertEquals($data['notes'], Task::first()->notes);
    }

    /** @test */
    public function a_task_can_have_a_due_date()
    {
        $this->actingAs($this->officeUser->user);

        $tomorrow = Carbon::tomorrow();
        $data = [
            'name' => 'test task',
            'due_date' => $tomorrow->format('m/d/Y'),
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $this->assertEquals($tomorrow->toDateTimeString(), Task::first()->due_date);
    }
    
    /** @test */
    public function a_task_can_have_an_assigned_user()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $data = [
            'name' => 'test task',
            'assigned_user_id' => $this->officeUser->user->id,
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $this->assertInstanceOf(OfficeUser::class, Task::first()->assignedUser);
    }

    /** @test */
    public function a_task_should_always_have_a_creator()
    {
        $this->actingAs($this->officeUser->user);

        $data = [
            'name' => 'test task',
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $this->assertEquals($this->officeUser->id, Task::first()->creator->id);
    }

    /** @test */
    public function an_office_user_can_update_a_task()
    {
        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create([
            'creator_id' => $this->officeUser->id,
            'assigned_user_id' => $this->officeUser->id,
        ]);

        $task->name = 'NEW NAME';

        $this->patchJson(route('business.tasks.update', ['task' => $task->id]), $task->toArray())
            ->assertStatus(200)
            ->assertSee('NEW NAME');

        $this->assertEquals('NEW NAME', $task->fresh()->name);
    }
    
    /** @test */
    public function a_task_should_track_edits_by_user()
    {
        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create([
            'creator_id' => $this->officeUser->id,
            'assigned_user_id' => $this->officeUser->id,
        ]);

        $this->assertCount(1, $task->editHistory);

        $task->name = 'NEW NAME';

        $this->patchJson(route('business.tasks.update', ['task' => $task->id]), $task->toArray())
            ->assertStatus(200);

        $this->assertCount(2, $task->fresh()->editHistory);
        
        $this->assertEquals($this->officeUser->id, $task->fresh()->editHistory()->latest()->get()->reverse()->first()->user_id);
    }
    
    // an_office_user_can_view_a_task
    // an_office_user_cannot_view_another_businesses_task
    // an_email_should_be_dispatched_to_the_user_assigned_to_a_task
    // an_office_user_should_see_the_number_of_pending_tasks_in_the_nav_menu
    // an_office_user_can_get_a_list_of_all_tasks
    // an_office_user_can_get_a_list_of_tasks_they_created
    // an_office_user_can_get_a_list_of_tasks_they_are_assigned_to
    // an_office_user_should_only_get_a_list_of_their_businesses_tasks
    // an_office_user_can_delete_a_task
}
