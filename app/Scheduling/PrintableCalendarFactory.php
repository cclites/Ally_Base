<?php

namespace App\Scheduling;

use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Business;


class PrintableCalendarFactory
{
    protected $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    protected $events;

    protected $start;

    protected $end;

    protected $filters;

    protected $clientId;

    protected $caregiverId;

    protected $business;

    protected $startDay;

    protected $endDay;

    protected $filteredEvents;

    const MONTH_ROW_INDEX = 6;

    const DAY_INDEX = 7;

    public function __construct($events, Carbon $start,  Carbon $end, ?string $filters, ?int $clientId, ?int $caregiverId, Business $business)
    {
        $this->events = $events;
        $this->start = $start;
        $this->end = $end;
        $this->filters = $filters;
        $this->clientId = $clientId;
        $this->caregiverId = $caregiverId;
        $this->business = $business;
    }

    public function generateMonthlyCalendar(): string
    {

        if($this->start->format('j') != 1){
            $this->start = $this->start->endOfMonth()->addMinute();
        }

        $this->startDay = $startDay = $this->start->copy();
        $this->endDay = $this->start->copy()->endOfMonth();

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

        // Represents the day of the week on which the month starts.
        // $sDay uses calendar_start_week as an offset for registries
        // that do no start their week on a Sunday

        $sDay = $startDay->dayOfWeek - ($this->business->chain->calendar_week_start);

        $monthName = $startDay->monthName;

        // represents an index to know to quit adding calendar days
        $daysInMonth = $startDay->daysInMonth;
        $year = $startDay->format("Y");

        $filteredEvents = $this->buildEventsMap();

        $html = $this->headerSpan() . "<h5>$monthName - $year</h5>";

        $html .= "<table>" .
                    "<thead>" .
                        "<tr>";

        foreach($this->orderDaysOfWeek() as $day){
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

    public function generateWeeklyCalendar(): string
    {
        $this->startDay = $startDay = $this->start;
        $this->endDay = $endDay = $this->end->subDay();

        $period = CarbonPeriod::create($startDay, $endDay);
        $daysOfWeek = [];

        foreach ($period as $date) {
            $daysOfWeek[] = $date->format('j');
        }

        $filteredEvents = $this->buildEventsMap();

        $html = $this->headerSpan() . "<h5>" . $startDay->format('F d, Y') . " - " . $endDay->format('F d, Y')  . "</h5>";

        $html .= "<table>" .
            "<thead>" .
            "<tr>";

        foreach($this->orderDaysOfWeek() as $day){
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

    public function generateDailyCalendar(): string
    {
        $startDay = $this->start->copy();
        $this->startDay = $startDay;
        $this->endDay = $endDay = $this->end;

        $dayString = $this->start->format("l");
        $day = $this->start->format("j");
        $filteredEvents = $this->buildEventsMap();

        $html = $this->headerSpan() . "<h5>" . $startDay->format('F d, Y') . "</h5>";

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

    public function buildEventsMap(): array
    {
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

    public function filterEvents(){

        $filters = explode(",", $this->filters);

        foreach($this->events as $event){

            if(in_array('OPEN', $filters) && $event['caregiver'] === 'OPEN'){
                $this->filteredEvents[] = $event;
                $this->caregiverId = 0;
                continue;
            }

            if(in_array($event['shift_status'], $filters)){
                $this->filteredEvents[] = $event;
            }

            //TODO: Add other filters
        }
    }

    public function headerSpan(): string
    {
        $html = "<div><h4>" . $this->business->name . "</h4>" .
                "<h6>". $this->business->getPhoneNumber()->number ."</h6>";

        if(isset($this->clientId)){
            $client = \App\Client::find($this->clientId);
            $client =  $client->nameLastFirst() . ", " . $client->getPhoneNumber()->number;
        }else{
            $client = "All Clients ";
        }

        if(isset($this->caregiverId) && $this->caregiverId != 0) {
            $caregiver = " visits by " . \App\Caregiver::find($this->caregiverId)->nameLastFirst();
        }else if($this->caregiverId == 0){
            $caregiver = "Open Shifts";
        }else{
            $caregiver = "visits by All Caregivers";
        }

        $html .= "<div style='text-align: center;'>Schedules for $client - $caregiver</div>";

        $html .= "</div>";

        return $html;
    }

    public function dateSpan($day): string
    {
        return "<div class='day'>$day</div>";
    }

    public function eventSpan($event): string
    {
        return "<div class='event'>" . $event['client'] . "<br>" . $event['caregiver']. "<br>" . $event['start_time'] . "<br>" . $event['end_time'] . "</div>";
    }

    public function orderDaysOfWeek(): array
    {

        if( $this->business->chain->calendar_week_start > 0){

            $slicedDays = array_slice($this->daysOfWeek, $this->business->chain->calendar_week_start);
            $reordered = array_splice($this->daysOfWeek, 0,  $this->business->chain->calendar_week_start);
            $daysArray = array_merge($slicedDays, $reordered);

            return $daysArray;
        }

        return $this->daysOfWeek;
    }
}
