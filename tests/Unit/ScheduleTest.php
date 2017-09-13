<?php

namespace Tests\Unit;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\ScheduleException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public $business;
    public $caregiver;
    public $client;
    public $scheduleAttributes;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();
        $this->client = factory(Client::class)->create();
        $this->scheduleAttributes = [
            'business_id' => $this->business->id,
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
        $schedule = factory(Schedule::class)->create($this->scheduleAttributes);
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testGetOccurrencesReturnsArrayOfDateTime()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = '2017-01-02';
        $enddate = '2017-03-31';

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrences();
        $this->assertEquals('array', gettype($occurrences));
        $this->assertInstanceOf(\DateTime::class, current($occurrences));
    }

    public function testGetOccurrencesForMonthlyInterval()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = '2017-01-02';
        $enddate = '2017-03-31';

        $schedule = factory(Schedule::class)->make([
                'rrule' => $rrule,
                'start_date' => $startdate,
                'end_date' => $enddate
            ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrences();
        $this->assertCount(3, $occurrences);
    }

    public function testGetOccurrencesForBiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'tu', 2);
        $startdate = '2017-01-03';
        $enddate = '2017-03-31';

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
            ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrences();
        $this->assertCount(7, $occurrences);
    }

    public function testGetOccurrencesForOnceWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'we');
        $startdate = '2017-01-04';
        $enddate = '2017-07-31';

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrences();
        $this->assertCount(30, $occurrences);
    }

    public function testGetOccurrencesForMultiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'mo,we,fr');

        $startdate = '2017-01-04';
        $enddate = '2017-07-31';

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrences();
        $this->assertCount(90, $occurrences);
    }

    public function testGetOccurrencesBetweenForShortWeeklyIntervals()
    {
        $startdate = '2017-01-06';
        $enddate = '2017-04-30';
        $rrule = $this->getrrule('weekly', 'fr');

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
            ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
        $this->assertCount(5, $occurrences);

        $startdate = '2017-01-03';
        $rrule = $this->getrrule('weekly', 'tu,th');

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
        $this->assertCount(9, $occurrences);

        $startdate = '2017-01-02';
        $rrule = $this->getrrule('weekly', 'mo,we,fr');

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-03-01', '2017-03-31');
        $this->assertCount(14, $occurrences);
    }

    public function testGetOccurrencesBetweenForLongWeeklyIntervals()
    {
        $startdate = '2015-12-07';
        $enddate = '2019-12-31';
        $rrule = $this->getrrule('weekly', 'mo,we');
        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-12-01', '2017-12-31');
        $this->assertCount(8, $occurrences);
    }

    public function testGetOccurrencesBetweenWithMidMonthStart()
    {
        $startdate = '2017-01-16';
        $enddate = '2017-04-30';
        $rrule = $this->getrrule('weekly', 'mo');

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-01-01', '2017-01-31');
        $this->assertCount(3, $occurrences);
    }

    public function testGetOccurrencesBetweenWithMidMonthEnd()
    {
        $startdate = '2017-01-02';
        $enddate = '2017-04-15';
        $rrule = $this->getrrule('weekly', 'mo');

        $schedule = factory(Schedule::class)->make([
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
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween('2017-04-01', '2017-04-30');
        $this->assertCount(3, $occurrences);
    }

    public function testGetOccurrencesBetweenAcceptsDateTimeObjects()
    {
        $startdate = '2017-01-06';
        $enddate = '2017-04-30';
        $rrule = $this->getrrule('weekly', 'fr');

        $schedule = factory(Schedule::class)->make([
               'rrule' => $rrule,
               'start_date' => $startdate,
               'end_date' => $enddate
           ] + $this->scheduleAttributes);

        $occurrences = $schedule->getOccurrencesBetween(new \DateTime('2017-03-01'), new \DateTime('2017-03-31 23:59:59'));
        $this->assertCount(5, $occurrences);
    }

    public function testScheduleCanHaveExceptions()
    {
        $schedule = $this->prepScheduleAndExceptions();
        $this->assertCount(2, $schedule->exceptions);
    }

    public function testScheduleExceptionIsExcludedInOccurrences()
    {
        $schedule = $this->prepScheduleAndExceptions();
        $invalid = ['2017-01-13', '2017-01-27'];

        foreach($schedule->getOccurrences() as $occurrence) {
            $this->assertNotContains($occurrence->format('Y-m-d'), $invalid);
        }
    }

    public function testScheduleCanHaveActivities()
    {

    }

    protected function getrrule($freq, $byday, $interval=1) {
        return sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%d', strtoupper($freq), strtoupper($byday), $interval);
    }


    protected function prepScheduleAndExceptions()
    {
        $startdate = '2017-01-06';
        $enddate   = '2017-04-30';
        $rrule     = $this->getrrule('weekly', 'fr');

        $schedule = factory(Schedule::class)->create([
                 'rrule'      => $rrule,
                 'start_date' => $startdate,
                 'end_date'   => $enddate
             ] + $this->scheduleAttributes);

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
