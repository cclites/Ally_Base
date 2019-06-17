<?php
namespace Tests\Feature;

use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\MaximumWeeklyHoursExceeded;
use App\Schedule;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ScheduleCreatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ScheduleCreator
     */
    public $scheduleCreator;

    public function setUp()
    {
        parent::setUp();
        $this->scheduleCreator = app()->make(ScheduleCreator::class);
    }

    public function testSingleScheduleCanBeCreated()
    {
        $this->scheduleCreator->startsAt(Carbon::now())
            ->duration(60)
            ->assignments(1, 1);

        $schedules = $this->scheduleCreator->create();

        $this->assertCount(1, $schedules);
        $this->assertInstanceOf(Schedule::class, $schedules[0]);
    }

    public function testCreateMethodReturnsCollection()
    {
        $this->scheduleCreator->startsAt(Carbon::now())
                              ->duration(60)
                              ->assignments(1, 1);

        $schedules = $this->scheduleCreator->create();
        $this->assertInstanceOf(Collection::class, $schedules);
    }

    public function testWeeklyRecurringRequiresByDays()
    {
        $this->expectException(InvalidScheduleParameters::class);
        $this->scheduleCreator->startsAt(Carbon::now())
                              ->duration(60)
                              ->assignments(1, 1)
                              ->interval('weekly', Carbon::now()->addMonth());

    }

    public function testDurationOfZeroThrowsException()
    {
        $this->expectException(InvalidScheduleParameters::class);
        $this->scheduleCreator->startsAt(Carbon::now())
                              ->duration(0)
                              ->assignments(1, 1);
        $schedules = $this->scheduleCreator->create();
    }

    public function testRecurringScheduleCanBeCreated()
    {
        $this->scheduleCreator->startsAt(new Carbon('2017-12-04'))
                              ->duration(60)
                              ->assignments(1, 1)
                              ->interval('weekly', new Carbon('2017-12-31'), ['mo', 'tu']);

        $schedules = $this->scheduleCreator->create();

        $this->assertCount(8, $schedules);
        $this->assertInstanceOf(Schedule::class, $schedules[0]);
    }

    public function testRecurringSchedulesBelongToTheSameGroup()
    {
        $this->scheduleCreator->startsAt(new Carbon('2017-12-04'))
            ->duration(60)
            ->assignments(1, 1)
            ->interval('weekly', new Carbon('2017-12-31'), ['mo', 'tu']);

        $schedules = $this->scheduleCreator->create();

        $this->assertGreaterThan(0, $schedules[0]->group_id);
        $this->assertCount(8, $schedules->where('group_id', $schedules[0]->group_id));
    }


    public function testStartDateNotInRecurringScheduleThrowsException()
    {
        $this->expectException(InvalidScheduleParameters::class);
        $this->scheduleCreator->startsAt(new Carbon('2017-12-01'))
                              ->duration(60)
                              ->assignments(1, 1)
                              ->interval('weekly', new Carbon('2017-12-31'), ['mo']);
        $schedules = $this->scheduleCreator->create();
    }

    public function testClientMaxWeeklyHoursExceededThrowsException()
    {
        $this->expectException(MaximumWeeklyHoursExceeded::class);

        $client = factory(Client::class)->create(['max_weekly_hours' => 10]);
        $this->scheduleCreator->startsAt(new Carbon('2017-12-04'))
                              ->duration(240)
            ->assignments($client->business_id, $client->id)
            ->interval('weekly', new Carbon('2017-12-31'), ['mo','tu','we'])
            ->create();
    }

    public function testClientMaxWeeklyHoursExceededIncludesExistingSchedules()
    {
        $client = factory(Client::class)->create(['max_weekly_hours' => 10]);

        $this->scheduleCreator->startsAt(new Carbon('2017-12-14'))
                              ->duration(60)
                              ->assignments($client->business_id, $client->id)
                              ->interval('weekly', new Carbon('2018-02-28'), ['th','fr','sa'])
                              ->create();

        $this->expectException(MaximumWeeklyHoursExceeded::class);
        $this->scheduleCreator->startsAt(new Carbon('2017-12-04'))
                              ->duration(180)
                              ->assignments($client->business_id, $client->id)
                              ->interval('weekly', new Carbon('2018-01-31'), ['mo','tu','we'])
                              ->create();
    }

    public function testOverrideAllowsClientMaxWeeklyHoursExceeded()
    {
        $client = factory(Client::class)->create(['max_weekly_hours' => 10]);
        $results = $this->scheduleCreator->startsAt(new Carbon('2017-12-04'))
                              ->duration(240)
                              ->assignments($client->business_id, $client->id)
                              ->interval('weekly', new Carbon('2018-01-31'), ['mo','tu','we'])
                              ->overrideMaxHours()
                              ->create();

        $this->assertTrue($results->count() > 0);
    }

    /**
     * @test
     */
    function a_schedule_created_in_EST_should_remain_the_same_time_in_EDT()
    {
        $timezone = 'America/New_York';
        $client = factory(Client::class)->create(['max_weekly_hours' => 10]);
        $client->business->update(['timezone' => $timezone]);

        $results = $this->scheduleCreator->startsAt(new Carbon('2019-03-06 12:00:00', $timezone))
            ->duration(240)
            ->assignments($client->business_id, $client->id)
            ->interval('weekly', new Carbon('2019-03-14 12:00:00', $timezone), ['we'])
            ->create();

        $this->assertEquals('2019-03-06 12:00:00', $results[0]->starts_at->toDateTimeString());
        $this->assertEquals('2019-03-13 12:00:00', $results[1]->starts_at->toDateTimeString());
    }

    /** @test */
    function creating_a_single_schedule_in_the_past_should_be_flagged_as_such()
    {
        $this->scheduleCreator->startsAt(Carbon::now()->subSecond(1))
            ->duration(60)
            ->assignments(1, 1);

        $schedules = $this->scheduleCreator->create();

        $this->assertCount(1, $schedules);
        $this->assertTrue($schedules[0]->added_to_past);
    }

    /** @test */
    function creating_a_single_schedule_after_the_current_time_is_not_added_to_past()
    {
        $this->scheduleCreator->startsAt(Carbon::now()->addSecond(1))
            ->duration(60)
            ->assignments(1, 1);

        $schedules = $this->scheduleCreator->create();

        $this->assertCount(1, $schedules);
        $this->assertFalse($schedules[0]->added_to_past);
    }

    /** @test */
    function creating_a_recurring_schedule_should_flag_any_past_times()
    {
        Carbon::setTestNow(Carbon::parse('2019-06-18 12:00:00')); // tuesday

        $this->scheduleCreator->startsAt(Carbon::yesterday())
                              ->duration(60)
                              ->assignments(1, 1)
                              ->interval('weekly', Carbon::today()->addWeeks(10), ['mo']);

        $schedules = $this->scheduleCreator->create();

        $this->assertCount(11, $schedules);

        $this->assertTrue($schedules[0]->added_to_past);
        $this->assertFalse($schedules[1]->added_to_past);
        $this->assertFalse($schedules[10]->added_to_past);
    }
}