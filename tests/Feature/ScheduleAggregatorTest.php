<?php
namespace Tests\Feature;

use App\Business;
use App\Caregiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Tests\TestCase;
use App\Scheduling\ScheduleAggregator;


class ScheduleAggregatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Client
     */
    public $client;

    /**
     * @var \App\Caregiver
     */
    public $caregiver;

    /**
     * @var \App\Business
     */
    public $business;

    /**
     * @var ScheduleAggregator
     */
    public $aggregator;

    public function setUp() : void
    {
        parent::setUp();

        $this->aggregator = new ScheduleAggregator();

        $this->business = factory(Business::class)->create(['timezone' => 'UTC']);

        $this->client = factory('App\Client')->create([
            'max_weekly_hours' => 40,
            'business_id' => $this->business->id,
        ]);

        $this->caregiver = factory('App\Caregiver')->create();
    }

    protected function createTimestamp($string = 'now')
    {
        return Carbon::parse($string, $this->business->timezone);
    }

    protected function createSchedule($date, $hours)
    {
        $creator = app()->make(ScheduleCreator::class);

        $creator->startsAt($date)
            ->duration($hours * 60)
            ->assignments($this->business->id, $this->client->id, $this->caregiver->id);

        return $creator->create()->first();
    }

    /** @test */
    public function test_it_can_count_the_total_hours_scheduled_in_a_given_week()
    {
        $this->createSchedule(Carbon::now(), 12);

        $this->assertEquals(12, $this->aggregator->getTotalScheduledHoursForWeekOf(Carbon::now(), $this->client->id));

        $this->createSchedule(Carbon::now(), 12);

        $this->assertEquals(24, $this->aggregator->getTotalScheduledHoursForWeekOf(Carbon::now(), $this->client->id));
    }

    public function test_aggregator_can_pull_schedules_occurring_between_two_timestamps()
    {
        $schedule1 = $this->createSchedule($this->createTimestamp('-2 hours'), 12); // should be found (occurs during)
        $schedule2 = $this->createSchedule($this->createTimestamp('-3 hours'), 2); // should be found (ends within 2 hours)
        $schedule3 = $this->createSchedule($this->createTimestamp('+1 hours'), 4); // should be found (starts within 2 hours)
        $schedule4 = $this->createSchedule($this->createTimestamp('+3 hours'), 4); // should not be found
        $schedule5 = $this->createSchedule($this->createTimestamp('-4 hours'), 1); // should not be found

        $start = $this->createTimestamp('-2 hours');
        $end = $this->createTimestamp('+2 hours');

        $schedules = $this->aggregator->getSchedulesBetween($start, $end);
        $this->assertNotNull($schedules->where('id', $schedule1->id)->first(), 'Schedule (1) starts before but occurs during not found.');
        $this->assertNotNull($schedules->where('id', $schedule2->id)->first(), 'Schedule (2) starts before but ends within 2 hours not found.');
        $this->assertNotNull($schedules->where('id', $schedule3->id)->first(), 'Schedule (3) starts within 1 hours not found.');
        $this->assertCount(3, $schedules);
    }

    public function test_aggregator_includes_daily_shifts()
    {
        // This test confirms a daily shift from 8AM to 8AM is still included in the aggregator get schedules between call.
        $this->business->timezone = 'UTC';
        Carbon::setTestNow('2018-05-18 15:00:00');
        $schedule = $this->createSchedule(Carbon::parse('2018-05-18 08:00:00'), 24); // should be found

        $start = Carbon::parse('-2 hours');
        $end = Carbon::parse('+2 hours');

        \DB::enableQueryLog();
        $schedules = $this->aggregator->getSchedulesBetween($start, $end);
        $log = \DB::getQueryLog();
        $this->assertCount(1, $schedules);
    }
}