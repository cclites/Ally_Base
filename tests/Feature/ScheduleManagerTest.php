<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Tests\TestCase;
use App\Scheduling\ScheduleAggregator;
use App\Schedule;
use App\Exceptions\MaximumWeeklyHoursExceeded;

class ScheduleManagerTest extends TestCase
{
    use RefreshDatabase;

    public $client;
    
    public $caregiver;
    
    public $business;

    public $aggregator;

    public $officeUser;

    public function setUp()
    {
        parent::setUp();
        $this->aggregator = new ScheduleAggregator();

        $this->client = factory('App\Client')->create();

        $this->business = $this->client->business;

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->caregivers()->save($this->caregiver);

        // init logged in office user
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->actingAs($this->officeUser->user);
        $this->officeUser->businesses()->attach($this->business->id);
    }

    public function createShift($date, $hours)
    {
        $creator = app()->make(ScheduleCreator::class);

        $creator->startsAt($date)
            ->duration($hours * 60)
            ->assignments($this->business->id, $this->client->id, $this->caregiver->id)
            ->rates(10.00, 15.00);

        $schedules = $creator->create();

        return Schedule::find($schedules[0]['id']);
    }

    /** @test */
    public function updating_a_schedule_should_fail_if_max_weekly_client_hours_are_exceeded()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $date = new Carbon('next tuesday');

        $this->createShift($date, 5);

        $shift = $this->createShift($date->addDay(), 4);

        $data = array_merge(array_only($shift->toArray(), [
            'client_id', 
            'hours_type',
            'notes',
            'overtime_duration',
        ]), [
            'duration' => 6 * 60, // = 11 hours total
            'starts_at' => $shift->starts_at->timestamp
        ]);

        $response = $this->JSON('put', route('business.schedule.update', ['schedule' => $shift->id]), $data);
        $response->assertStatus(449);

        $data['duration'] = 5 * 60; // = 10 hours total

        $this->JSON('put', route('business.schedule.update', ['schedule' => $shift->id]), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function creating_a_schedule_should_fail_if_max_weekly_client_hours_are_exceeded()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $date = new Carbon('next tuesday');

        $shift = $this->createShift($date->addDay(), 5);

        $data = array_merge(array_only($shift->toArray(), [
            'caregiver_id',
            'caregiver_rate',
            'client_id', 
            'hours_type',
            'notes',
            'overtime_duration',
            'provider_fee',
        ]), [
            'duration' => 6 * 60, // = 11 hours total
            'starts_at' => $shift->starts_at->timestamp
        ]);

        $this->JSON('post', route('business.schedule.store'), $data)
            ->assertStatus(449);

        $data['duration'] = 5 * 60; // = 10 hours total

        $this->JSON('post', route('business.schedule.store'), $data)
            ->assertSee('shift has been created')
            ->assertStatus(201);
    }

    /** @test */
    public function a_business_can_bulk_update_duration_of_schedules()
    {
        $date = new Carbon('next tuesday');
        $this->createShift($date, 3);
        $this->createShift($date->addDay(), 3);
        $this->createShift($date->addDay(), 3);

        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $data = [
            'bydays' => ["MO", "TU", "WE", "TH", "FR", "SA", "SU"],
            'client_id' => $this->client->id,
            'start_date' => $weekStart->format('m/d/Y'), //'04/15/2018',
            'end_date' => $weekEnd->format('m/d/Y'), //'04/21/2018',
            'new_duration' => 4 * 60,
            'new_start_time' => '01:00',
        ];

        $this->post(route('business.schedule.bulk_update'), $data)
            ->assertStatus(200);
        
        $this->assertEquals(12, $this->aggregator->getTotalScheduledHoursForWeekOf($date, $this->client->id));
    }

    /** @test */
    public function bulk_updating_schedules_should_fail_if_any_max_weekly_client_hours_are_exceeded()
    {
        $this->disableExceptionHandling();
        $this->client->update(['max_weekly_hours' => 10]);

        $date = new Carbon('next tuesday');
        $this->createShift($date, 3);
        $this->createShift($date->addDay(), 3);
        $this->createShift($date->addDay(), 3);

        $date2 = new Carbon('next month');
        $this->createShift($date2, 3);

        $this->assertEquals(9, $this->aggregator->getTotalScheduledHoursForWeekOf($date, $this->client->id));

        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $data = [
            'bydays' => ["MO", "TU", "WE", "TH", "FR", "SA", "SU"],
            'client_id' => $this->client->id,
            'start_date' => $weekStart->format('m/d/Y'), //'04/15/2018',
            'end_date' => $date2->format('m/d/Y'), //'04/21/2018',
            'new_duration' => 5 * 60,
            'new_start_time' => '01:00',
        ];

        $this->post(route('business.schedule.bulk_update'), $data)
            ->assertStatus(449);

        $this->assertEquals(9, $this->aggregator->getTotalScheduledHoursForWeekOf($date, $this->client->id));
    }
}