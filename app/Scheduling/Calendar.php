<?php

namespace App\Scheduling;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Responses\Resources\ScheduleEvents;

class Calendar extends Model
{
    protected $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function generateMonthlyCalendar( $events, Carbon $start,  Carbon $end)
    {
        $startDay = $start->endOfMonth()->addMinute();
        $endDay = $end->startOfMonth();
        $rowIndex = 6;
        $dayIndex = 7;
        $counter = 0;
        $monthIndex = 0;
        $filteredEvents = $this->buildEventsMap($events, $startDay, $endDay);
        $sDay = $startDay->dayOfWeek;
        $monthName = $startDay->monthName;
        $daysInMonth = $startDay->daysInMonth;

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

    public function buildEventsMap($events, Carbon $startDay, Carbon $endDay){

        $eventMap = [];

        foreach($events as $event){

            if($event['start'] > $startDay && $event['end'] < $endDay){

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
}
