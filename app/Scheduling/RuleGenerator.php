<?php

namespace App\Scheduling;

use When\When;

class RuleGenerator extends When
{
    //make rrule format
    public function getRule()
    {
        $rrule = array();

        if($this->freq) $rrule['FREQ']=$this->freq;
        if($this->until) $rrule['UNTIL']=$this->until->format('Ymd\THis\Z');
        if($this->count) $rrule['COUNT']=$this->count;
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
            case 'weekly':
                $this->interval(1);
                $this->freq('WEEKLY');
                break;
            case 'biweekly':
                $this->interval(2);
                $this->freq('WEEKLY');
                break;
            case 'monthly':
                $this->interval(1);
                $this->freq('MONTHLY');
                break;
            case 'bimonthly':
                $this->interval(2);
                $this->freq('MONTHLY');
                break;
            case 'quarterly':
                $this->interval(3);
                $this->freq('MONTHLY');
                break;
            case 'semiannually':
                $this->interval(6);
                $this->freq('MONTHLY');
                break;
            case 'annually':
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
                        return 'weekly';
                    case 2:
                        return 'biweekly';
                }
                break;
            case 'MONTHLY':
                switch($this->interval) {
                    case 1:
                        return 'monthly';
                    case 2:
                        return 'bimonthly';
                    case 3:
                        return 'quarterly';
                    case 6:
                        return 'semiannually';
                    case 12:
                        return 'annually';
                }
        }
        return null;
    }

}
