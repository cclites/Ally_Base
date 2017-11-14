<?php
namespace App\Reports;

use App\Business;
use App\Schedule;
use App\Scheduling\AllyFeeCalculator;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use Carbon\Carbon;

class ScheduledVsActualReport extends BaseReport
{
    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * @var null|array
     */
    protected $events = null;

    /**
     * Prepare the shifts query and inject the business
     */
    public function __construct(Business $business)
    {
        $this->query = Shift::where('business_id',  $business->id);
        $this->business = $business;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Limit rows between two dates
     *
     * @param string|\DateTime|null $start If null, leave starting period unlimited
     * @param string|\DateTime|null $end If null, leave ending period unlimited
     * @return $this
     */
    public function between($start = null, $end = null)
    {
        if ($start) {
            $start = (new Carbon($start))->setTimezone('UTC');
        }
        if ($end) {
            $end = (new Carbon($end))->setTimezone('UTC');
        }

        if ($start && $end) {
            $this->query->whereBetween('checked_in_time', [$start, $end]);
        }
        elseif ($start) {
            $this->query->where('checked_in_time', '>=', $start);
        }
        else {
            $this->query->where('checked_in_time', '<=', $end);
        }

        $aggregator = new ScheduleAggregator();
        foreach($this->business->schedules as $schedule) {
            $clientName = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $caregiverName = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $title = $clientName . ' (' . $caregiverName . ')';
            $aggregator->add($title, $schedule);
        }

        $this->events = $aggregator->events($start, $end);

        return $this;
    }


    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        if (!$this->generated) {
            $shifts = $this->query->get();
            // Filter out events that were clocked in to (comparing against $shifts)
            $events = array_filter($this->events, function($event) use (&$shifts) {
                 $matchingShiftsBySchedule = $shifts->where('schedule_id', $event['schedule_id']);
                 foreach ($matchingShiftsBySchedule as $shift) {
                     /** @var Shift $shift */
                     $eventTime = Carbon::instance($event['start']);
                     $shiftTime = (new Carbon($shift->checked_in_time, 'UTC'))->setTimezone($this->business->timezone);
                     // clocked in to within 2.5 hours of start time, filter from array
                     if ($eventTime->diffInMinutes($shiftTime) <= 150) return false;
                 }

                 if ($event['caregiver_id']) {
                     $matchingShiftsByClient = $shifts->where('client_id', $event['client_id']);
                     $matchingShiftsByClientCaregiver = $matchingShiftsByClient->where('caregiver_id', $event['caregiver_id']);
                     foreach ($matchingShiftsByClientCaregiver as $shift) {
                         /** @var Shift $shift */
                         $eventTime = Carbon::instance($event['start']);
                         $shiftTime = (new Carbon($shift->checked_in_time, 'UTC'))->setTimezone($this->business->timezone);
                         // clocked in to within 2.5 hours of start time, filter from array
                         if ($eventTime->diffInMinutes($shiftTime) <= 150) return false;
                     }
                 }

                 return true;
            });
            // Map the report fields
            $events = array_map(function($event) {
                $schedule = Schedule::with(['client', 'caregiver'])->find($event['schedule_id']);
                $hours = round($schedule->duration / 60, 2);
                $caregiverRate = $schedule->getCaregiverRate();
                $providerFee = $schedule->getProviderFee();
                $allyFee = AllyFeeCalculator::getFee($schedule->client, null, $caregiverRate + $providerFee);
                $hourlyTotal = $caregiverRate + $providerFee + $allyFee;
                return array_merge($event, [
                    'start' => Carbon::instance($event['start'])->toIso8601String(),
                    'end' => Carbon::instance($event['end'])->toIso8601String(),
                    'client' => $schedule->client,
                    'caregiver' => $schedule->caregiver,
                    'hours' => $hours,
                    'caregiver_rate' => number_format($caregiverRate, 2),
                    'provider_fee' => number_format($providerFee, 2),
                    'ally_fee' => number_format($allyFee, 2),
                    'hourly_total' => number_format($hourlyTotal, 2),
                    'shift_total' => number_format($hourlyTotal * $hours, 2),
                    'hours_type' => $schedule->hours_type,
                ]);
            }, $events);
            $this->rows = collect(array_values($events));
        }
        return $this->rows;
    }

}