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


}