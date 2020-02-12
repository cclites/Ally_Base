<?php


namespace App\Reports;

use App\Caregiver;
use App\Client;
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

        $this->start = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $this->end = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');

        $this->query->whereHas('shifts', function ($q){
            $q->whereBetween('checked_in_time', [$this->start, $this->end]);
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
     * @return \Illuminate\Support\Collection
     */
    protected function results() : iterable
    {
        return $this->query
            ->groupBy('caregivers.id')
            ->get()
            ->map(function(Caregiver $caregiver){
                $worked = 0;
                $futureScheduled = 0;

                foreach($caregiver->shifts()->whereBetween( 'checked_in_time', [$this->start, $this->end] )->where('checked_out_time', '!=', null )->get() as $shift) {
                    $worked += $shift->duration();
                }

                foreach($caregiver->shifts()->whereBetween( 'checked_in_time', [$this->start, $this->end] )->where('checked_out_time', null )->get() as $shift) {
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

                $clients = $this->getClientsForCaregiver($caregiver);

                return [
                    'firstname'=>$caregiver->first_name,
                    'lastname'=>$caregiver->last_name,
                    'worked' => $this->roundTimeToNearestInterval($worked),
                    'future_scheduled' => $this->roundTimeToNearestInterval($futureScheduled),
                    'total' => $this->roundTimeToNearestInterval($total),
                    'clients' => $clients
                ];
            })
            ->values();
    }

    /**
     * @param float $time
     * @param int $interval
     * @return float
     */
    private function roundTimeToNearestInterval($time, $interval = 25){
        $decimalPart = ($time - floor($time)) * 100;
        return floatval(floor($time).'.'.(ceil($decimalPart / $interval) * $interval));
    }
    private function getClientsForCaregiver(Caregiver $caregiver){
        /*$clients = $caregiver->clients()->whereHas('shifts', function($query){

        });*/
        return $caregiver->clients()->get()->map(function(Client $client) use ($caregiver){
            $worked = 0;
            $futureScheduled = 0;

            foreach($client->shifts()->where('caregiver_id', $caregiver->id)->whereBetween( 'checked_in_time', [$this->start, $this->end] )->where('checked_out_time', '!=', null )->get() as $shift) {
                $worked += $shift->duration();
            }

            foreach($client->shifts()->where('caregiver_id', $caregiver->id)->whereBetween( 'checked_in_time', [$this->start, $this->end] )->where('checked_out_time', null )->get() as $shift) {
                $worked += $shift->duration();
                $futureScheduled += $shift->remaining();
            }
            $futureScheduled += $client->schedules()
                ->where('caregiver_id', $caregiver->id)
                ->startsBetweenDates($this->timezone, 'now', $this->end)
                ->sum('duration');
            $total = round($worked + $futureScheduled, 2);
            return [
                'name' => $client->name,
                'worked' => $this->roundTimeToNearestInterval($worked),
                'future_scheduled' => $this->roundTimeToNearestInterval($futureScheduled),
                'total' => $this->roundTimeToNearestInterval($total)
            ];
        })->values();
    }
}