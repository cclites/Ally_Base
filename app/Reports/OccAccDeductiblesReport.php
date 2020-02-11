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
        $this->query = Shift::with([ 'business', 'caregiver' => function( $q ){

            return $q->where( 'has_occ_acc', 1 );
        }]);
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
    public function forWeekEndingAt( $end )
    {
        $this->end_date   = $end;
        $this->start_date = Carbon::parse( $end )->subWeek()->format( 'm-d-Y' ); // format may be unneccesary here

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
        return $this->query()
            // ->forBusinesses([ $this->businessId ])
            ->betweenDates( $this->start_date, $this->end_date )
            ->forCaregiver( $this->caregiverId )
            ->get()
            ->map( function ( $shift ){

                // CG name
                // Registry
                // Hours Worked
                // OccAcc Deduction Total

                return [

                    'caregiver_name' => $shift->caregiver ? $shift->caregiver->name : 'NO NAME??',
                    'registry'       => $shift->business ? $shift->business->name : 'NO NAME??',
                    'hours_worked'   => $shift->duration, // should be a count or aggregate..
                    'deduction'      => 1337
                ];
            });
    }
}
