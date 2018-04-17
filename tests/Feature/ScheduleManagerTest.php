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

        $this->client = factory('App\Client')->create();

        $this->business = $this->client->business;

        $this->caregiver = factory('App\Caregiver')->create();

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
            ->assignments($this->business->id, $this->client->id, $this->caregiver->id);

        $schedules = $creator->create();

        return Schedule::find($schedules[0]['id']);
    }

    /** @test */
    public function updating_a_schedule_should_throw_an_exception_if_max_weekly_client_hours_are_exceeded()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $weekStart = new Carbon('next tuesday');

        $this->createShift($weekStart, 5);

        $shift = $this->createShift($weekStart->addDay(), 4);

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

        $this->put(route('business.schedule.update', ['schedule' => $shift->id]), $data)
            ->assertStatus(449);

        $data['duration'] = 5 * 60; // = 10 hours total

        $this->put(route('business.schedule.update', ['schedule' => $shift->id]), $data)
            ->assertStatus(200);
    }
}