<?php

namespace Tests\Feature;

use App\Caregiver;
use App\User;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use Carbon\Carbon;
use App\OfficeUser;
use App\Mail\AssignedTaskEmail;
use App\Business;

class ManageTasksTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();
        $this->createBusinessWithUsers();
    }

    public function setupMultiBusinessTasks()
    {
        \Mail::fake();  // added this to speed up tests

        $user2 = factory(OfficeUser::class)->create();
        $user2->businesses()->attach($this->business->id);
        $user3 = factory(OfficeUser::class)->create();
        $business2 = factory(Business::class)->create();
        $user3->businesses()->attach($business2->id);

        factory(Task::class, 4)->create(['creator_id' => $this->officeUser->id, 'business_id' => $this->business->id, 'assigned_user_id' => $this->officeUser->id]);
        factory(Task::class, 3)->create(['creator_id' => $user2->id, 'business_id' => $this->business->id, 'assigned_user_id' => $this->officeUser->id]);
        factory(Task::class, 2)->create(['creator_id' => $user3->id, 'business_id' => $business2->id, 'assigned_user_id' => $user3->id]);
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

        $task = Task::first();
        $this->assertInstanceOf(User::class, $task->assignedUser);
        $this->assertEquals('Staff', $task->assignedType);
    }

    /** @test */
    public function a_task_can_be_assigned_to_a_caregiver()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $data = [
            'name' => 'test task',
            'assigned_user_id' => $this->caregiver->user->id,
        ];

        $this->postJson(route('business.tasks.store'), $data)
            ->assertStatus(200);

        $task = Task::first();
        $this->assertInstanceOf(User::class, $task->assignedUser);
        $this->assertEquals('Caregiver', $task->assignedType);
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
    public function an_office_user_can_mark_a_task_complete()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create();

        $data = $task->toArray();
        $data['completed'] = 1;

        $this->patchJson(route('business.tasks.update', ['task' => $task->id]), $data)
            ->assertStatus(200);

        $this->assertNotNull($task->fresh()->completed_at);

        $data['completed'] = 0;

        $this->patchJson(route('business.tasks.update', ['task' => $task->id]), $data)
            ->assertStatus(200);

        $this->assertNull($task->fresh()->completed_at);
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

    /** @test */
    public function an_office_user_can_view_a_task()
    {
        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create();

        $this->getJson(route('business.tasks.update', ['task' => $task->id]))
            ->assertStatus(200)
            ->assertJsonFragment($task->toArray());
    }

    /** @test */
    public function an_office_user_cannot_view_another_businesses_task()
    {
        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $task = factory(Task::class)->create([
            'creator_id' => $user2->id,
            'business_id' => $business2->id,
        ]);

        $this->actingAs($this->officeUser->user);

        $this->getJson(route('business.tasks.update', ['task' => $task->id]))
            ->assertStatus(403);
    }

    /** @test */
    public function an_email_should_be_dispatched_when_the_assigned_user_is_set_or_changed()
    {
        \Mail::fake();

        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);

        // temp disable this feature
        \Mail::assertNotSent(AssignedTaskEmail::class);
//        \Mail::assertSent(AssignedTaskEmail::class, function ($mail) use ($task) {
//            return $mail->task->id === $task->id && $task->assigned_user_id == $this->officeUser->id;
//        });

        $task->update(['assigned_user_id' => $user2->id]);

        // temp disable this feature
        \Mail::assertNotSent(AssignedTaskEmail::class);
//        \Mail::assertSent(AssignedTaskEmail::class, function ($mail) use ($task, $user2) {
//            return $mail->task->id === $task->id && $task->assigned_user_id == $user2->id;
//        });
    }

    /** @test */
    public function an_email_should_not_be_sent_if_the_assigned_user_does_not_change()
    {
        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);

        \Mail::fake();

        $task->update(['notes' => 'other update']);

        \Mail::assertNotSent(AssignedTaskEmail::class);
    }

    /** @test */
    public function an_email_should_not_be_sent_if_the_assigned_user_is_cleared()
    {
        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);

        \Mail::fake();

        $task->update(['assigned_user_id' => null]);

        \Mail::assertNotSent(AssignedTaskEmail::class);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_all_tasks()
    {
        $this->actingAs($this->officeUser->user);

        $task1 = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);
        $task2 = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);
        $task3 = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);

        $this->assertCount(3, Task::all());

        $this->getJson(route('business.tasks.index'))
            ->assertStatus(200)
            ->assertJsonFragment($task1->toArray())
            ->assertJsonFragment($task2->toArray())
            ->assertJsonFragment($task3->toArray())
            ->assertJsonCount(3);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_tasks_they_created()
    {
        $this->setupMultiBusinessTasks();
        $this->assertCount(9, Task::all());

        $this->actingAs($this->officeUser->user);

        $this->assertCount(4, $this->officeUser->tasks);

        $this->getJson(route('business.tasks.index') . '?created=1')
            ->assertStatus(200)
            ->assertJsonCount(4);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_tasks_they_are_assigned_to()
    {
        $this->setupMultiBusinessTasks();
        $this->assertCount(9, Task::all());

        $this->actingAs($this->officeUser->user);

        $this->assertCount(7, $this->officeUser->user->dueTasks);

        $this->getJson(route('business.tasks.index') . '?assigned=1')
            ->assertStatus(200)
            ->assertJsonCount(7);
    }

    /** @test */
    public function an_office_user_should_only_get_a_list_of_their_businesses_tasks()
    {
        $this->setupMultiBusinessTasks();
        $this->assertCount(9, Task::all());

        $this->actingAs($this->officeUser->user);

        $this->getJson(route('business.tasks.index'))
            ->assertStatus(200)
            ->assertJsonCount(7);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_pending_tasks()
    {
        $this->setupMultiBusinessTasks();
        $this->assertCount(9, Task::all());

        $this->actingAs($this->officeUser->user);

        $this->officeUser->user->dueTasks()->first()->markComplete();

        $this->getJson(route('business.tasks.index') . '?pending=1')
            ->assertStatus(200)
            ->assertJsonCount(6);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_overdue_tasks()
    {
        $this->withoutExceptionHandling();

        $this->setupMultiBusinessTasks();
        $this->assertCount(9, Task::all());

        $this->actingAs($this->officeUser->user);

        $this->officeUser->user->dueTasks()->first()->update(['due_date' => Carbon::yesterday()]);

        $this->getJson(route('business.tasks.index') . '?overdue=1')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function an_office_user_can_delete_a_task()
    {
        $this->actingAs($this->officeUser->user);

        $task = factory(Task::class)->create();

        $this->deleteJson(route('business.tasks.destroy', ['task' => $task->id]))
            ->assertStatus(200);

        $this->assertCount(0, Task::all());
    }

    /** @test */
    public function a_office_user_cannot_delete_another_businesses_tasks()
    {
        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $task = factory(Task::class)->create([
            'creator_id' => $user2->id,
            'business_id' => $business2->id,
        ]);

        $this->actingAs($this->officeUser->user);

        $this->deleteJson(route('business.tasks.destroy', ['task' => $task->id]))
            ->assertStatus(403);
    }

    /** @test */
    public function an_office_user_cannot_update_another_businesses_task()
    {
        $this->withExceptionHandling();

        $business2 = factory('App\Business')->create();
        $user2 = factory('App\OfficeUser')->create();
        $user2->businesses()->attach($business2->id);

        $task = factory(Task::class)->create([
            'creator_id' => $user2->id,
            'business_id' => $business2->id,
        ]);

        $this->actingAs($this->officeUser->user);

        $response = $this->patchJson(route('business.tasks.update', ['task' => $task->id]), $task->toArray());
        $response->assertStatus(403);
    }

    /** @test */
    public function a_caregiver_can_see_tasks_assigned_to_them()
    {
        $this->withoutExceptionHandling();

        \Mail::fake();

        factory(Task::class, 4)->create([
            'creator_id' => $this->officeUser->id,
            'business_id' => $this->business->id,
            'assigned_user_id' => $this->caregiver->id
        ]);
        //$caregiver2 = factory(Caregiver::class)->create();

        $this->assertCount(4, Task::all());

        $this->actingAs($this->caregiver->user);

        $this->getJson(route('caregivers.tasks'))
            ->assertStatus(200)
            ->assertJsonCount(4);
    }
}
