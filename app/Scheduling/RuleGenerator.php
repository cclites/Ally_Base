<?php

namespace App\Scheduling;

use When\When;

class RuleGenerator extends When
{
    const LOCAL_DATETIME_FORMAT = 'Ymd\THis';
    const UTC_DATETIME_FORMAT = 'Ymd\THis\Z';

    const INTERVAL_WEEKLY = 'weekly';
    const INTERVAL_BIWEEKLY = 'biweekly';
    const INTERVAL_MONTHLY = 'monthly';
    const INTERVAL_BIMONTHLY = 'bimonthly';
    const INTERVAL_QUARTERLY = 'quarterly';
    const INTERVAL_SEMIANNUALLY = 'semiannually';
    const INTERVAL_ANNUALLY = 'annually';

    protected $dayMapping = [
        0 => 'su',
        1 => 'mo',
        2 => 'tu',
        3 => 'we',
        4 => 'th',
        5 => 'fr',
        6 => 'sa',
    ];

    //make rrule format
    public function getRule()
    {
        $rrule = array();

        if($this->freq) $rrule['FREQ'] =$this->freq;
        if($this->until) $rrule['UNTIL'] = self::getLocalTime($this->until);
        if($this->count) $rrule['COUNT'] = $this->count;
        if($this->interval) $rrule['INTERVAL']=$this->interval;
//        $byday = array();
//        array_walk($this->byday, function($item, $key) use (&$byday) {$byday[]=substr($item, -2, 2);});

        if($this->bydays) $rrule['BYDAY']=implode(',', array_map(function($day) {
            return substr($day, -2);
        }, $this->bydays));
        if($this->bymonthdays) $rrule['BYMONTHDAY']=implode(',', $this->bymonthdays);
        if($this->byyeardays) $rrule['BYYEARDAY']=implode(',', $this->byyeardays);
        if($this->byweeknos) $rrule['BYWEEKNO']=implode(',', $this->byweeknos);
        if($this->bymonths) $rrule['BYMONTH']=implode(',', $this->bymonths);
        if($this->bysetpos) $rrule['BYSETPOS']=implode(',', $this->bysetpos);

        $data = array();
        foreach($rrule as $key => $val) {
            $data[] = $key.'='. strtoupper($val);
        }
        $data = implode(';', $data);
        return $data;
    }

    public function setIntervalType($type)
    {
        switch(strtolower($type)) {
            case self::INTERVAL_WEEKLY:
                $this->interval(1);
                $this->freq('WEEKLY');
                break;
            case self::INTERVAL_BIWEEKLY:
                $this->interval(2);
                $this->freq('WEEKLY');
                break;
            case self::INTERVAL_MONTHLY:
                $this->interval(1);
                $this->freq('MONTHLY');
                break;
            case self::INTERVAL_BIMONTHLY:
                $this->interval(2);
                $this->freq('MONTHLY');
                break;
            case self::INTERVAL_QUARTERLY:
                $this->interval(3);
                $this->freq('MONTHLY');
                break;
            case self::INTERVAL_SEMIANNUALLY:
                $this->interval(6);
                $this->freq('MONTHLY');
                break;
            case self::INTERVAL_ANNUALLY:
                $this->interval(12);
                $this->freq('MONTHLY');
                break;
        }
        return $this;
    }

    public function getIntervalType()
    {
        switch(strtoupper($this->freq)) {
            case 'WEEKLY':
                switch($this->interval) {
                    case 1:
                        return self::INTERVAL_WEEKLY;
                    case 2:
                        return self::INTERVAL_BIWEEKLY;
                }
                break;
            case 'MONTHLY':
                switch($this->interval) {
                    case 1:
                        return self::INTERVAL_MONTHLY;
                    case 2:
                        return self::INTERVAL_BIMONTHLY;
                    case 3:
                        return self::INTERVAL_QUARTERLY;
                    case 6:
                        return self::INTERVAL_SEMIANNUALLY;
                    case 12:
                        return self::INTERVAL_ANNUALLY;
                }
        }
        return null;
    }

    public static function getLocalTime(\DateTime $date) {
        return $date->format(self::LOCAL_DATETIME_FORMAT);
    }

    public static function getUTCDate(\DateTime $date) {
        $date = clone $date;
        $date->setTimezone(new \DateTimeZone('UTC'));
        return $date->format(self::UTC_DATETIME_FORMAT);
    }

    public function setWeekdays(int... $days): void
    {
        $bydays = array_map(function($int) {
            return $this->dayMapping[$int];
        }, $days);
        $this->byday(implode(',', $bydays), ',');
    }

    public function getWeekdays(): array
    {
        return array_map(function($str) {
            return array_search(preg_replace('/\d/', '', $str), $this->dayMapping);
        }, $this->bydays ?? []);
    }
}
