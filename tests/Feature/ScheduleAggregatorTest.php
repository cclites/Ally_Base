<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Tests\TestCase;
use App\Scheduling\ScheduleAggregator;


class ScheduleAggregatorTest extends TestCase
{
    use RefreshDatabase;

    public $client;
    
    public $caregiver;
    
    public $business;

    public $aggregator;

    public function setUp()
    {
        parent::setUp();

        $this->aggregator = new ScheduleAggregator();

        $this->client = factory('App\Client')->create([
            'max_weekly_hours' => 40,
        ]);

        $this->business = $this->client->business;

        $this->caregiver = factory('App\Caregiver')->create();
    }

    public function createShift($date, $hours)
    {
        $creator = app()->make(ScheduleCreator::class);

        $creator->startsAt($date)
            ->duration($hours * 60)
            ->assignments($this->business->id, $this->client->id, $this->caregiver->id);

        return $creator->create();
    }

    /** @test */
    public function test_it_can_count_the_total_hours_scheduled_in_a_given_week()
    {
        $this->createShift(Carbon::now(), 12);

        $this->assertEquals(12, $this->aggregator->getTotalScheduledHoursForWeekOf(Carbon::now(), $this->client->id));

        $this->createShift(Carbon::now(), 12);

        $this->assertEquals(24, $this->aggregator->getTotalScheduledHoursForWeekOf(Carbon::now(), $this->client->id));
    }
}