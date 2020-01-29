<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\TaskEditHistory;
use App\Business;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public $business;
    public $officeUser;

    public function setUp() : void
    {
        parent::setUp();

        $this->caregiver = factory('App\Caregiver')->create();
        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function it_belongs_to_a_business()
    {
        $task = factory(Task::class)->create();

        $this->assertInstanceOf(Business::class, $task->business);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $task = factory(Task::class)->create();

        $this->assertEquals($this->officeUser->id, $task->creator->id);
    }

    /** @test */
    public function it_can_have_an_assigned_user()
    {
        $task = factory(Task::class)->create(['assigned_user_id' => $this->officeUser->id]);

        $this->assertEquals($this->officeUser->id, $task->assignedUser->id);
    }

    /** @test */
    public function it_has_a_last_edit_record()
    {
        $task = factory(Task::class)->create(['creator_id' => $this->officeUser->id]);

        $this->assertInstanceOf(TaskEditHistory::class, $task->lastEdit);
    }
}
