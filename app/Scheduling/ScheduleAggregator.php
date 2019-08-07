<?php

namespace App\Scheduling;

use App\Schedule;
use Carbon\Carbon;

/**
 * Class ScheduleAggregator
 * @package App\Scheduling
 * @deprecated   May no longer be needed since Schedules are one-off, we can aggregate using simple queries using the Schedule repository
 */
class ScheduleAggregator
{
    /**
     * @var array
     */
    protected $eagerLoaded = ['client', 'caregiver', 'shifts'];

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var bool
     */
    protected $onlyStartTime = true;

    /**
     * Start a fresh query
     */
    public function fresh()
    {
        $this->query = null;
        return $this;
    }

    /**
     * Access the query builder object used for aggregation
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function query()
    {
        if (!$this->query) $this->query = Schedule::with($this->eagerLoaded)->orderBy('starts_at');
        return $this->query;
    }

    /**
     * Filter the aggregation query
     *
     * @param $field
     * @param $delimiter
     * @param $value
     * @return $this
     */
    public function where($field, $delimiter, $value = null)
    {
        if ($delimiter === null && $value === null) {
            $this->query()->whereNull($field);
            return $this;
        }
        $this->query()->where($field, $delimiter, $value);
        return $this;
    }

    /**
     * Only include the schedule if it has one of the given statuses.
     *
     * @param string|array $status
     * @return $this
     */
    public function onlyStatus($status)
    {
        if (! is_array($status)) {
            $status = [$status];
        }

        $this->query()->whereIn('status', $status);
        return $this;
    }

    /**
     * Only include the schedule if the date range matches the start time, not accounting for the end time
     *
     * @param bool $bool
     * @return $this
     */
    public function onlyStartTime(bool $bool=true)
    {
        $this->onlyStartTime = $bool;
        return $this;
    }

    /**
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSchedulesStartingBetween(Carbon $start, Carbon $end)
    {
        return $this->query()->whereBetween('starts_at', [$start, $end])->get();
    }

    /**
     * Get all schedule models occurring between $start and $end
     *
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSchedulesBetween(Carbon $start, Carbon $end)
    {
        switch(\DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $endFormat = "`starts_at` + INTERVAL `duration` MINUTE";
                break;
            case 'sqlite':
                $endFormat = "datetime(starts_at, '+' || duration || ' minutes')";
                break;
        }

        return $this->query()
            ->whereRaw(
                '( (starts_at >= ? AND starts_at <= ?) OR (starts_at < ? AND ' . $endFormat . ' >= ?) )',
                [$start, $end, $start, $start]
            )->get();
    }


    /**
     * Get all schedule models occurring in the future that have not been clocked in to
     *
     * @param \Carbon\Carbon $until
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getFutureShifts(Carbon $until = null)
    {
        if (!$until) $until = Carbon::parse('+3 years');
        return $this->query()->whereBetween('starts_at', [Carbon::now(), $until])->doesntHave('shifts')->get();
    }


    /**
     * Return an array format optimized for Event APIs
     *
     * @param string|\DateTime $start_date
     * @param string|\DateTime $end_date
     * @param string $timezone
     * @param int $limitPerEvent
     *
     * @return array
     */
    public function events(Carbon $start, Carbon $end)
    {
        if ($this->onlyStartTime) {
            $schedules = $this->getSchedulesStartingBetween($start, $end);
        } else {
            $schedules = $this->getSchedulesBetween($start, $end);
        }

        return $schedules->map(function(Schedule $schedule) {
            return [
                'schedule_id' => $schedule->id,
                'title'       => $this->resolveEventTitle($schedule),
                'start'       => $schedule->starts_at->format('c'),
                'end'         => $schedule->starts_at->copy()->addMinutes($schedule->duration)->format('c'),
                'duration'    => $schedule->duration,
                'checked_in'  => $schedule->isClockedIn(),
                'client_id'   => $schedule->client_id,
                'caregiver_id'=> $schedule->caregiver_id,
                'client_name' => optional($schedule->client)->name,
                'caregiver_name' => $schedule->caregiver_id ? optional($schedule->caregiver)->name : 'No Caregiver Assigned',
                'caregiver_phones' => optional($schedule->caregiver)->phoneNumbers,
            ];
        });
    }

    /**
     * Gets the sum of hours of all the schedules during the  
     * week of the given date for the given client id.
     *
     * @param Carbon $date
     * @param int $client_id
     * @return float
     */
    public function getTotalScheduledHoursForWeekOf(Carbon $date, $client_id)
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $schedules = $this->fresh()
            ->where('client_id', $client_id)
            ->getSchedulesStartingBetween($weekStart, $weekEnd);

        return $schedules->sum('duration') / 60;
    }
}
