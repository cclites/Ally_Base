<?php

namespace App\Scheduling;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Responses\Resources\ScheduleEvents;

class Calendar extends Model
{
    protected $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    protected $events;

    protected $start;

    protected $end;

    protected $filters;

    protected $startDay;

    protected $endDay;

    protected $filteredEvents;

    const MONTH_ROW_INDEX = 6;

    const DAY_INDEX = 7;

    public function __construct($events, Carbon $start,  Carbon $end, ?string $filters)
    {
        $this->events = $events;
        $this->start = $start;
        $this->end = $end;
        $this->filters = $filters;
    }

    public function generateMonthlyCalendar( )
    {
        $this->startDay = $startDay = $this->start->endOfMonth()->addMinute();
        $this->endDay = $this->end->startOfMonth();

        /**************************************************************************
         * The following are counters used to render the calendar, and
         * determine the correct start day on the calendar, and which day to
         * place events.
         */

        // rowIndex represents a calendar row, used for rendering calendar
        $rowIndex = self::MONTH_ROW_INDEX;

        // dayIndex represents a calendar day, used for rendering calendar
        $dayIndex = self::DAY_INDEX;

        // counter represents the int value of the day of the week.
        $counter = 0;

        // monthIndex is an index representing the day of the month.
        $monthIndex = 0;
        /*****************************************************************************/

        //represents the day of the week on which the month starts
        $sDay = $startDay->dayOfWeek;

        $monthName = $startDay->monthName;

        // represents an index to know to quit adding calendar days
        $daysInMonth = $startDay->daysInMonth;

        $filteredEvents = $this->buildEventsMap();

        $html = "<h2>$monthName</h2>";

        $html .= "<table>" .
                    "<thead>" .
                        "<tr>";

        foreach($this->daysOfWeek as $day){
            $html .=        "<th>$day</th>";
        }

        $html .=        "</tr>" .
                    "</thead>"  .
                    "<tbody>";

        for($i = 0; $i < $rowIndex; $i += 1){

            $html .= "<tr>";

            for($j = 0; $j< $dayIndex; $j += 1){

                if($counter < $sDay){
                    $html .= "<td>&nbsp;</td>";
                }elseif($counter == $sDay){
                    $html .= "<td>" .
                             $this->dateSpan(++$monthIndex);
                    if(isset($filteredEvents[$monthIndex])){
                        $html .= $filteredEvents[$monthIndex];
                    }
                    $html .= "</td>";
                }elseif($monthIndex < $daysInMonth){
                    $html .= "<td>" .
                        $this->dateSpan(++$monthIndex);
                    if(isset($filteredEvents[$monthIndex])){
                        $html .= $filteredEvents[$monthIndex];
                    }
                    $html .= "</td>";
                }

                $counter++;
            }

            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        return $html;
    }

    public function generateWeeklyCalendar(){

        $this->startDay = $startDay = $this->start;
        $this->endDay = $endDay = $this->end->subDay();

        $period = CarbonPeriod::create($startDay, $endDay);
        $daysOfWeek = [];

        foreach ($period as $date) {
            $daysOfWeek[] = $date->format('j');
        }

        $filteredEvents = $this->buildEventsMap();

        $html = "<h2>" . $startDay->format('m-d-y') . " - " . $endDay->format('m-d-y')  . "</h2>";

        $html .= "<table>" .
            "<thead>" .
            "<tr>";

        foreach($this->daysOfWeek as $day){
            $html .=        "<th>$day</th>";
        }

        $html .= "</tr>" .
                 "</thead>"  .
                 "<tbody>" .
                 "<tr>";

        foreach ($daysOfWeek as $day){

            $html .= "<td>" .
                $this->dateSpan($day);
            if(isset($filteredEvents[$day])){
                $html .= $filteredEvents[$day];
            }
            $html .= "</td>";
        }

        $html .= "</tr></tbody></table>";

        return $html;
    }

    public function generateDailyCalendar(){

        $startDay = $this->start->copy();
        $this->startDay = $startDay;
        $this->endDay = $endDay = $this->end;

        $dayString = $this->start->format("l");
        $day = $this->start->format("j");
        $filteredEvents = $this->buildEventsMap();

        $html = "<h2>" . $startDay->format('F d, Y') . "</h2>";

        $html .= "<table>" .
                 "<thead>" .
                 "<tr>" .
                 "<th>$dayString</th>" .
                 "</tr>" .
                 "</thead>" .
                 "<tbody>" .
                 "<tr>" .
                 "<td>";

        if(isset($filteredEvents[$day])){
            $html .= $filteredEvents[$day];
        }

         $html .= "</td>" .
                 "</tr>" .
                 "</tbody>" .
                 "</table>";

        return $html;
    }

    public function buildEventsMap(){

        $eventMap = [];

        if(filled($this->filters)){
           $this->filterEvents();
        }else{
            $this->filteredEvents = $this->events;
        }

        foreach($this->filteredEvents as $event){

            if($event['start'] > $this->startDay && $event['end'] < $this->endDay){

                $key = Carbon::parse($event['start'])->format('j');

                if(!array_key_exists($key, $eventMap)){
                    $eventMap[$key] = $this->eventSpan($event);
                }else{
                    $eventMap[$key] .= $this->eventSpan($event);
                }
            }
        }

        return $eventMap;
    }

    public function dateSpan($day){
        return "<div class='day'>$day</div>";
    }

    public function eventSpan($event){
        return "<div class='event' style='background-color:" . $event['backgroundColor'] . ";'>" . $event['client'] . "<br>" . $event['caregiver']. "<br>" . $event['start_time'] . "<br>" . $event['end_time'] . "</div>";
    }

    public function filterEvents(){

        $filters = explode(",", $this->filters);

        foreach($this->events as $event){

            if(in_array('OPEN', $filters) && $event['caregiver'] === 'OPEN'){
                $this->filteredEvents[] = $event;
                continue;
            }

            if(in_array($event['shift_status'], $filters)){
                $this->filteredEvents[] = $event;
            }
        }
    }
}
