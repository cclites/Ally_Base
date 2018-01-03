<?php

namespace Tests\Model;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Exceptions\MissingTimezoneException;
use App\Schedule;
use App\ScheduleException;
use Carbon\Carbon;
use DateTime;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ScheduleTest
 * Every test runs on multiple businesses to verify different environments / time zones
 *
 *
 * @package Tests\Unit
 */
class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public $businesses;
    public $caregiver;
    public $client;
    public $scheduleAttributes;

    public function setUp()
    {
        parent::setUp();
        $this->businesses = collect([
            factory(Business::class)->create(['timezone' => 'UTC']),
            factory(Business::class)->create(['timezone' => 'America/New_York']),
            factory(Business::class)->create(['timezone' => 'America/Los_Angeles']),
        ]);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->client = factory(Client::class)->create();
        $this->scheduleAttributes = [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
        ];
    }

    public function testScheduleTestCanBeInitialized()
    {
        $this->assertTrue(true);
    }

    public function testScheduleCanBeCreated()
    {
        $schedule = factory(Schedule::class)->create(['business_id' => $this->businesses[0]->id] + $this->scheduleAttributes);
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testGetOccurrencesReturnsArrayOfDateTime()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = '2017-01-02';
        $enddate = '2017-03-31';

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);
            $occurrences = $schedule->getOccurrences();
            $this->assertEquals('array', gettype($occurrences));
            $this->assertInstanceOf(\DateTime::class, current($occurrences));
        }
    }

    public function testGetOccurrencesForMonthlyInterval()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = '2017-01-02';
        $enddate = '2017-03-31';

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrences();
            $this->assertCount(3, $occurrences);
        }
    }

    public function testGetOccurrencesForBiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'tu', 2);
        $startdate = '2017-01-03';
        $enddate = '2017-03-31';

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrences();
            $this->assertCount(7, $occurrences);
        }
    }

    public function testGetOccurrencesForOnceWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'we');
        $startdate = '2017-01-04';
        $enddate = '2017-07-31';

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrences();
            $this->assertCount(30, $occurrences);
        }
    }

    public function testGetOccurrencesForMultiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'mo,we,fr');

        $startdate = '2017-01-04';
        $enddate = '2017-07-31';

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrences();
            $this->assertCount(90, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenForShortWeeklyIntervals()
    {
        foreach($this->businesses as $business) {
            $startdate = '2017-01-06';
            $enddate = '2017-04-30';
            $rrule = $this->getrrule('weekly', 'fr');

            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
            $this->assertCount(5, $occurrences);

            $startdate = '2017-01-03';
            $rrule = $this->getrrule('weekly', 'tu,th');

            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
            $this->assertCount(9, $occurrences);

            $startdate = '2017-01-02';
            $rrule = $this->getrrule('weekly', 'mo,we,fr');

            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
            $this->assertCount(14, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenForLongWeeklyIntervals()
    {
        $startdate = '2015-12-07';
        $enddate = '2019-12-31';
        $rrule = $this->getrrule('weekly', 'mo,we');

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-12-01', '2017-12-31');
            $this->assertCount(8, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenWithMidMonthStart()
    {
        $startdate = '2017-01-16';
        $enddate = '2017-04-30';
        $rrule = $this->getrrule('weekly', 'mo');

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-01-01', '2017-01-31');
            $this->assertCount(3, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenWithMidMonthEnd()
    {
        foreach($this->businesses as $business) {
            $startdate = '2017-01-02';
            $enddate = '2017-04-15';
            $rrule = $this->getrrule('weekly', 'mo');
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-04-01', '2017-04-30');
            $this->assertCount(2, $occurrences);

            $startdate = '2017-01-02';
            $enddate = '2017-04-17';
            $rrule = $this->getrrule('weekly', 'mo');

            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween('2017-04-01', '2017-04-30');
            $this->assertCount(3, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenAcceptsDateTimeObjects()
    {
        $startdate = '2017-01-06';
        $enddate = '2017-04-30';
        $rrule = $this->getrrule('weekly', 'fr');

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate
                ] + $this->scheduleAttributes);

            $occurrences = $schedule->getOccurrencesBetween(new \DateTime('2017-03-01'),
                new \DateTime('2017-03-31 23:59:59'));
            $this->assertCount(5, $occurrences);
        }
    }

    public function testScheduleCanHaveExceptions()
    {
        $schedule = $this->prepScheduleAndExceptions();
        $this->assertCount(2, $schedule->exceptions);
    }

    public function testScheduleExceptionIsExcludedInOccurrences()
    {
        foreach ($this->businesses as $business) {
            $schedule = $this->prepScheduleAndExceptions($business->id);
            $invalid = ['2017-01-13', '2017-01-27'];

            foreach ($schedule->getOccurrences() as $occurrence) {
                $this->assertNotContains($occurrence->format('Y-m-d'), $invalid);
            }
        }
    }

    public function testScheduleCanHaveActivities()
    {
        $business = factory(Business::class)->create();
        $activity1 = factory(Activity::class)->create(['business_id' => $business->id]);
        $activity2 = factory(Activity::class)->create(['business_id' => $business->id]);
        $schedule = $this->prepASchedule($business->id);

        $schedule->activities()->attach($activity1);
        $schedule->activities()->attach($activity2);

        $this->assertCount(2, $schedule->activities);
    }

    public function testScheduleIncludesOccurrencesThatHaveStartedButNotFinished() {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = '2017-01-02';
        $enddate = '2017-03-31';
        $time = '09:00:00';
        $duration = 240;
        $test_duration = 30; // must be less than duration

        foreach($this->businesses as $business) {
            $schedule = factory(Schedule::class)->make([
                    'business_id' => $business->id,
                    'rrule' => $rrule,
                    'start_date' => $startdate,
                    'end_date' => $enddate,
                    'time' => $time,
                    'duration' => $duration
                ] + $this->scheduleAttributes);

            $start = new Carbon($startdate . ' ' . $time, $business->timezone);
            $start->addMinutes($test_duration);
            $end = new Carbon($startdate . ' ' . $time, $business->timezone);
            $end->addDays(1);

            $occurrences = $schedule->getOccurrencesBetween($start, $end);
            $this->assertCount(1, $occurrences, 'Failed matching initial past start time');

            $start->addMinutes($duration - $test_duration - 1);
            $occurrences = $schedule->getOccurrencesBetween($start, $end);
            $this->assertCount(1, $occurrences, 'Failed matching far past start time');

            $start->addMinutes(2);
            $occurrences = $schedule->getOccurrencesBetween($start, $end);
            if (count($occurrences)) dump($start, $end, $occurrences);
            $this->assertCount(0, $occurrences, 'Failed asserting 0 occurrences after end time');
        }
    }

    public function testMissingBusinessTimezoneShouldThrowException()
    {
        $business = factory(Business::class)->create(['timezone' => '']);
        $this->expectException(MissingTimezoneException::class);
        $schedule = $this->prepASchedule($business->id);
        $schedule->getOccurrences();
    }

    protected function getrrule($freq, $byday, $interval=1) {
        return sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%d', strtoupper($freq), strtoupper($byday), $interval);
    }

    protected function prepASchedule($business_id=null)
    {
        $startdate = '2017-01-06';
        $enddate   = '2017-04-30';
        $rrule     = $this->getrrule('weekly', 'fr');

        $schedule = factory(Schedule::class)->create([
                 'business_id' => $business_id ?? $this->businesses[0]->id,
                 'rrule'      => $rrule,
                 'start_date' => $startdate,
                 'end_date'   => $enddate
             ] + $this->scheduleAttributes);
        return $schedule;
    }

    protected function prepScheduleAndExceptions($business_id=null)
    {
        $schedule = $this->prepASchedule();

        $exception1 = new ScheduleException([
            'date' => '2017-01-13'
        ]);

        $exception2 = new ScheduleException([
            'date' => '2017-01-27'
        ]);

        $schedule->exceptions()->save($exception1);
        $schedule->exceptions()->save($exception2);

        return $schedule;
    }
}
