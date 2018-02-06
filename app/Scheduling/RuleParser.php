<?php
namespace App\Scheduling;

use RRule\RRule;

class RuleParser
{
    protected $start;
    protected $rrule;
    protected $parser;

    public function setRule(\DateTime $start, $rrule)
    {
        $this->parser = new RRule($rrule, clone $start);
        return $this;
    }

    public function getOccurrencesBetween(\DateTime $start_date, \DateTime $end_date, $limit = null)
    {
        $occurrences = $this->parser->getOccurrencesBetween($start_date, $end_date, $limit);
        return $occurrences;
    }

    public function humanReadable($opts = []) {
        return $this->parser->humanReadable($opts);
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

    protected function getrrule($freq, $byday, $interval=1) {
        return sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%d', strtoupper($freq), strtoupper($byday), $interval);
    }
}