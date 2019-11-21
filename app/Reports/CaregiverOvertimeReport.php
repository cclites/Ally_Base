<?php


namespace App\Reports;

use App\Caregiver;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;

class CaregiverOvertimeReport extends BaseReport
{

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var int
     */
    protected $caregiver_id;

    /**
     * @var date
     */
    protected $end;

    /**
     * @var date
     */
    protected $start;

    /**
     * CaregiverOvertimeReport constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::forRequestedBusinesses()
                        ->with('schedules')
                        ->ordered();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query() : self
    {
        return $this->query;
    }

    public function setTimezone(string $timezone) : self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Build the query
     *
     * @param string $start
     * @param string $end
     * @param int|null $caregiver_id
     * @param string|null $active
     * @return CaregiverOvertimeReport
     */
    public function applyFilters(string $start, string $end, ?int $caregiver_id, ?string $status) : self
    {
        $this->end = $end;
        $this->start = $start;

        $start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');

        $this->query->whereHas('shifts', function ($q) use ($start, $end) {
            $q->whereBetween('checked_in_time', [$start, $end]);
        });

        if(filled($caregiver_id)){
            $this->query->where('caregivers.id', $caregiver_id);
        }

        if (filled($status)) {
            $this->query->whereHas('user', function($q) use ($status) {
                $q->where('active', $status == 'active');
            });
        }

        return $this;
    }
    /**
     * process the results
     *
     * @return Collection
     */
    protected function results() : iterable
    {
        $start = (new Carbon($this->start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($this->end . ' 23:59:59', $this->timezone))->setTimezone('UTC');

        $record = $this->query
                    ->groupBy('caregivers.id')
                    ->get()->map(function(Caregiver $caregiver) use ($start, $end ){

                    $worked = 0;
                    $futureScheduled = 0;
                    $total = 0;

                    foreach($caregiver->shifts()->whereBetween( 'checked_in_time', [$start, $end] )->where('checked_out_time', '!=', null )->get() as $shift) {
                        $worked += $shift->duration();
                    }

                    foreach($caregiver->shifts()->whereBetween( 'checked_in_time', [$start, $end] )->where('checked_out_time', null )->get() as $shift) {
                        $worked += $shift->duration();
                        $futureScheduled += $shift->remaining();
                    }

                    $duration = Schedule::startsBetweenDates($this->timezone, 'now', $this->end)
                            ->where('caregiver_id', $caregiver->id)
                            ->sum('duration');

                    $futureScheduled += $duration;

                    $worked = round($worked, 2);
                    $futureScheduled = round($futureScheduled / 60, 2);
                    $total = round($worked + $futureScheduled, 2);

                    return [
                        'firstname'=>$caregiver->first_name,
                        'lastname'=>$caregiver->last_name,
                        'worked' => $worked,
                        'future_scheduled' => $futureScheduled,
                        'total' => $total,
                    ];

            });

             return $record->values();
    }
}