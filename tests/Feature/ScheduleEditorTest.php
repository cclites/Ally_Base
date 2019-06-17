<?php

namespace Tests\Feature;

use App\Business;
use App\Schedule;
use App\ScheduleGroup;
use App\Scheduling\ScheduleEditor;
use App\Client;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleEditorTest extends TestCase
{
    use RefreshDatabase;

    /** @var ScheduleEditor */
    private $editor;

    protected function setUp()
    {
        parent::setUp();
        $this->editor = new ScheduleEditor();
    }

    /**
     * @test
     */
    function a_single_schedule_can_be_updated()
    {
        $schedule = factory(Schedule::class)->create();

        $data = [
            'starts_at' => '2019-01-01 23:30:00',
            'duration' => 60,
        ];

        $notes = 'Hello World';

        $this->editor->updateSingle($schedule, $data, $notes);

        $this->assertEquals($data['starts_at'], $schedule->starts_at->toDateTimeString());
        $this->assertEquals($data['duration'], $schedule->duration);
        $this->assertEquals($notes, $schedule->notes);
    }

    /**
     * @test
     */
    function a_single_schedule_update_removes_the_group()
    {
        $schedule = factory(Schedule::class)->create(['group_id' => 1]);
        $data = [
            'starts_at' => '2019-01-01 23:30:00',
            'duration' => 60,
        ];

        $this->editor->updateSingle($schedule, $data, '');

        $this->assertSame(null, $schedule->group_id);
    }

    /**
     * @test
     */
    function a_group_update_handles_relative_time_differences()
    {
        $group = factory(ScheduleGroup::class)->create();
        $schedule1 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-01 00:00:00',
            'group_id' => $group->id
        ]);
        $schedule2 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-03 00:00:00',
            'group_id' => $group->id
        ]);

        $this->editor->updateGroup($group, $schedule1, ['starts_at' => '2019-01-01 12:00:00'], '');

        $this->assertEquals('2019-01-03 12:00:00', $schedule2->fresh()->starts_at->toDateTimeString());
    }

    /**
     * @test
     */
    function a_future_update_does_not_touch_past_data()
    {
        $group = factory(ScheduleGroup::class)->create();
        $schedule1 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-01 00:00:00',
            'group_id' => $group->id
        ]);
        $schedule2 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-03 00:00:00',
            'group_id' => $group->id
        ]);
        $schedule3 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-08 00:00:00',
            'group_id' => $group->id
        ]);

        $this->editor->updateFuture($group, $schedule2, ['starts_at' => '2019-01-03 12:00:00'], '');

        $this->assertEquals('2019-01-01 00:00:00', $schedule1->fresh()->starts_at->toDateTimeString(), 'The past schedule should NOT have been updated.');
        $this->assertEquals('2019-01-03 12:00:00', $schedule2->fresh()->starts_at->toDateTimeString(), 'The starting schedule did not get updated.');
        $this->assertEquals('2019-01-08 12:00:00', $schedule3->fresh()->starts_at->toDateTimeString(), 'The future schedule did not get updated.');
        $this->assertGreaterThan($group->id, $schedule3->fresh()->group_id, 'The future schedule should have a new group ID');

    }

    /**
     * @test
     */
    function an_update_to_a_specific_weekday_does_not_touch_other_weekdays()
    {
        $group = factory(ScheduleGroup::class)->create();
        $schedule1 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-01 06:00:00',
            'group_id' => $group->id,
            'weekday' => 2,
        ]);
        $schedule2 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-03 06:00:00',
            'group_id' => $group->id,
            'weekday' => 4,
        ]);
        $schedule3 = factory(Schedule::class)->create([
            'starts_at' => '2019-01-08 06:00:00',
            'group_id' => $group->id,
            'weekday' => 2,
        ]);


        $this->editor->updateGroup($group, $schedule1, ['starts_at' => '2019-01-01 12:00:00'], '', [], 2);

        $this->assertEquals('2019-01-03 06:00:00', $schedule2->fresh()->starts_at->toDateTimeString(), 'The Thursday schedule should NOT get updated.');
        $this->assertEquals('2019-01-08 12:00:00', $schedule3->fresh()->starts_at->toDateTimeString(), 'The future Tuesday schedule did not get updated.');
        $this->assertGreaterThan($group->id, $schedule3->fresh()->group_id, 'The future Tuesday schedule should have a new group ID');
    }

    /**
     * @test
     */
    function an_update_should_not_affect_current_timings_across_DST()
    {
        $business = factory(Business::class)->create(['timezone' => 'America/New_York']);
        $client = factory(Client::class)->create(['business_id' => $business->id]);
        $group = factory(ScheduleGroup::class)->create();
        $schedule1 = factory(Schedule::class)->create([
            'starts_at' => '2019-03-09 06:00:00',
            'group_id' => $group->id,
            'weekday' => 2,
            'client_id' => $client->id,
            'business_id' => $business->id,
        ]);

        $this->editor->updateGroup($group, $schedule1, ['starts_at' => '2019-03-10 06:00:00']);
        $this->assertEquals('06:00:00', $schedule1->fresh()->starts_at->toTimeString());
    }

    /**
     * @test
     */
    function an_update_should_not_affect_future_timings_across_DST()
    {
        $business = factory(Business::class)->create(['timezone' => 'America/New_York']);
        $client = factory(Client::class)->create(['business_id' => $business->id]);
        $group = factory(ScheduleGroup::class)->create();
        $schedule1 = factory(Schedule::class)->create([
            'starts_at' => '2019-03-06 06:00:00',
            'group_id' => $group->id,
            'weekday' => 2,
            'client_id' => $client->id,
            'business_id' => $business->id,
        ]);
        $schedule3 = factory(Schedule::class)->create([
            'starts_at' => '2019-03-13 06:00:00',
            'group_id' => $group->id,
            'weekday' => 2,
            'client_id' => $client->id,
            'business_id' => $business->id,
        ]);

        $this->editor->updateGroup($group, $schedule1, ['starts_at' => '2019-03-06 12:00:00']);
        $this->assertEquals('12:00:00', $schedule3->fresh()->starts_at->toTimeString());
    }

    /** @test */
    function editing_a_single_schedule_should_flag_when_start_date_added_to_past()
    {
        $schedule = factory(Schedule::class)->create();

        $data = [
            'starts_at' => Carbon::yesterday(),
            'duration' => 60,
        ];

        $this->editor->updateSingle($schedule, $data, null);

        $this->assertEquals($data['starts_at'], $schedule->fresh()->starts_at->toDateTimeString());
        $this->assertEquals($data['duration'], $schedule->duration);
        $this->assertTrue($schedule->fresh()->added_to_past);
    }
}
