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
     * CaregiverOvertimeReport constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::forRequestedBusinesses()
                        ->with('shifts')
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
        $record = $this->query
                    ->groupBy('caregivers.id')
                    ->get()->map(function(Caregiver $caregiver){

                    $worked = 0;
                    $scheduled = 0;
                    $total = 0;
                    $totalScheduled = 0;

                    foreach($caregiver->shifts as $shift){
                        $totalScheduled += $shift->hours;
                    }

                    foreach($caregiver->shifts->where('checked_out_time', '!=', null) as $shift) {
                        $worked += $shift->duration();
                    }

                    foreach($caregiver->shifts->where('checked_out_time', null) as $shift) {
                        $worked += $shift->duration();
                        $scheduled += $shift->remaining();
                    }

                    $scheduledHrs =  Schedule::future($this->timezone, $this->end)
                            ->where('caregiver_id', $caregiver->id)
                            ->sum('duration');

                    $scheduled += $scheduledHrs;

                    $worked = round($worked / 60, 2);
                    $scheduled = round($scheduled / 60, 2);
                    $total = round($worked + $scheduled, 2);


                    return [
                        'firstname'=>$caregiver->first_name,
                        'lastname'=>$caregiver->last_name,
                        'worked' => $worked,
                        'scheduled' => $scheduled,
                        'total' => $total
                    ];

            });

             return $record->values();
    }
}