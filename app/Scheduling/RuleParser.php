<?php
namespace App\Scheduling;

use RRule\RRule;

class RuleParser
{
    protected $start;
    protected $rrule;
    protected $parser;

    /**
     * Factory Method
     *
     * @param \DateTime $start
     * @param $rrule
     * @return \App\Scheduling\RuleParser
     */
    public static function create(\DateTime $start, $rrule)
    {
        return (new self)->setRule($start, $rrule);
    }

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

    protected function getrrule($freq, $byday, $interval=1) {
        return sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%d', strtoupper($freq), strtoupper($byday), $interval);
    }
}