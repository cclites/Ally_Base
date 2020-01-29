<?php

namespace Tests\Unit;

use App\Scheduling\RuleParser;
use Carbon\Carbon;
use Tests\TestCase;

class RuleParserTest extends TestCase
{
    /**
     * @var RuleParser
     */
    public $parser;

    protected $timezones = ['UTC', 'America/New_York', 'America/Los_Angeles'];

    public function setUp() : void
    {
        parent::setUp();
        $this->parser = new RuleParser();
    }

    public function testGetOccurrencesBetweenReturnsArrayOfDateTime()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = new \DateTime('2017-01-02');
        $enddate = new \DateTime('2017-03-31');

        $occurrences = $this->parser->setRule($startdate, $rrule)
                                    ->getOccurrencesBetween($startdate, $enddate);
        $this->assertInternalType('array', $occurrences);
        $this->assertInstanceOf(\DateTime::class, current($occurrences));
    }

    public function testLimitRestrictsNumberOfResults()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';
        $startdate = new \DateTime('2017-01-02');
        $enddate = new \DateTime('2018-01-02');

        $occurrences = $this->parser->setRule($startdate, $rrule)
                                    ->getOccurrencesBetween($startdate, $enddate, 8);
        $this->assertCount(8, $occurrences);
    }

    public function testGetOccurrencesForMonthlyInterval()
    {
        $rrule = 'FREQ=MONTHLY;BYMONTHDAY=2;INTERVAL=1';

        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-02', $timezone);
            $enddate = new Carbon('2017-03-31', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($startdate, $enddate);

            $this->assertCount(3, $occurrences);
        }
    }

    public function testGetOccurrencesForBiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'tu', 2);

        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-02', $timezone);
            $enddate = new Carbon('2017-03-31', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($startdate, $enddate);

            $this->assertCount(7, $occurrences);
        }
    }

    public function testGetOccurrencesForOnceWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'we');

        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-04', $timezone);
            $enddate = new Carbon('2017-07-31', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($startdate, $enddate);

            $this->assertCount(30, $occurrences);
        }
    }

    public function testGetOccurrencesForMultiWeeklyInterval()
    {
        $rrule = $this->getrrule('weekly', 'mo,we,fr');

        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-04', $timezone);
            $enddate = new Carbon('2017-07-31', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($startdate, $enddate);

            $this->assertCount(90, $occurrences);
        }
    }

    public function testGetOccurrencesBetweenWithMidMonthStart()
    {
        $rrule = $this->getrrule('weekly', 'mo');

        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-16', $timezone);
            $enddate = new Carbon('2017-01-31', $timezone);
            $searchStart = new Carbon('2017-01-01', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($searchStart, $enddate);

            $this->assertCount(3, $occurrences);
        }
    }

    public function testOccurrencesMaintainTimezone()
    {
        $rrule = $this->getrrule('weekly', 'mo');
        foreach($this->timezones as $timezone) {
            $startdate = new Carbon('2017-01-01', $timezone);
            $enddate = new Carbon('2017-01-31', $timezone);
            $occurrences = $this->parser->setRule($startdate, $rrule)
                                        ->getOccurrencesBetween($startdate, $enddate, 1);

            $this->assertEquals($timezone, current($occurrences)->getTimezone()->getName());
        }
    }

    protected function getrrule($freq, $byday, $interval=1) {
        return sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%d', strtoupper($freq), strtoupper($byday), $interval);
    }
}
