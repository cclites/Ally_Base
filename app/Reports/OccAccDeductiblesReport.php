<?php

namespace App\Reports;

use App\Shift;
use Carbon\Carbon;

class OccAccDeductiblesReport extends BusinessResourceReport
{
    /**
     * The begin date.
     *
     * @var string
     */
    protected $start_date;

    /**
     * The end date.
     *
     * @var string
     */
    protected $end_date;

    /**
     * The caregiver ID.
     *
     * @var int
     */
    protected $caregiverId;

    /**
     * The business ID.
     *
     * @var int
     */
    protected $businessId;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Shift::with(['caregiver', 'client'])
            ->whereAwaitingCaregiverDeposit();
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
     * Filter the results to a week starting from one point in time
     *
     * @param string $start
     * @return $this
     */
    public function forWeekStartingAt( $start )
    {
        $this->start_date = $start;
        $this->end_date   = Carbon::parse( $start )->addWeek()->format( 'm-d-Y' ); // format may be unneccesary here

        return $this;
    }

    /**
     * Set filter for caregiver.
     *
     * @param $id
     * @return $this
     */
    public function forCaregiver($id)
    {
        $this->caregiverId = $id;

        return $this;
    }

    /**
     * Set filter for business.
     *
     * @param $id
     * @return $this
     */
    public function forBusiness($id)
    {
        $this->businessId = $id;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $detail = $this->query()
            ->forBusinesses([$this->businessId])
            ->betweenDates($this->start_date, $this->end_date)
            ->forCaregiver($this->caregiverId)
            ->orderBy('checked_in_time')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'caregiver_name' => $item->caregiver->name,
                    'caregiver_id' => $item->caregiver->id,
                    'caregiver_rate' => $item->caregiver_rate,
                    'client_name' => $item->client->name,
                    'client_id' => $item->client->id,
                    // TODO: verify that this is the correct way to determine pay method
                    'pay_method' => $item->daily_rates ? 'Daily' : 'Hourly',
                    // -------
                    // TODO: implement overtime hours:
                    'hours_regular' => $item->duration(),
                    'hours_overtime' => 0,
                    // -------
                    'caregiver_total' => $item->costs()->getCaregiverCost(true),
                    'checked_in_time' => $item->checked_in_time->format('c'),
                    'checked_out_time' => optional($item->checked_out_time)->format('c'),
                    'total' => $item->costs()->getTotalCost(),
                ];
            });

        $summary = $detail->groupBy('caregiver_id')
            ->map(function ($item) {
                return [
                    'caregiver_id' => $item->first()['caregiver_id'],
                    'caregiver_name' => $item->first()['caregiver_name'],
                    'hours_regular' => $item->sum('hours_regular'),
                    'hours_overtime' => $item->sum('hours_overtime'),
                    'caregiver_total' => $item->sum('caregiver_total'),
                    'checked_in_time' => $item->min('checked_in_time'),
                    'checked_out_time' => $item->max('checked_out_time'),
                ];
            })->values();

        return collect(compact(['summary', 'detail']));
    }
}
