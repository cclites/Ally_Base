<?php
namespace App\Scheduling;

use RRule\RRule;

class RuleParser
{
    protected $start;
    protected $rrule;
    protected $parser;

    public function __construct(\DateTime $start, $rrule)
    {
        $this->start = clone $start;
        $this->rrule = $rrule;

        $this->parser = new RRule($this->rrule, $this->start);
    }

    public function getOccurrencesBetween(\DateTime $start_date, \DateTime $end_date, $limit = null)
    {
        $occurrences = $this->parser->getOccurrencesBetween($start_date, $end_date, $limit);
//        return array_filter($occurrences, function($date) use ($start_date, $end_date) {
//            return $date >= $start_date && $date <= $end_date;
//        });
        return $occurrences;
    }

    public function humanReadable($opts = []) {
        return $this->parser->humanReadable($opts);
    }
}