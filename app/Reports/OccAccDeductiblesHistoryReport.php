<?php

namespace App\Reports;

use App\Caregiver;
use App\OccAccDeductible;
use Carbon\Carbon;

class OccAccDeductiblesHistoryReport extends BusinessResourceReport
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
     * The businesses IDs.
     *
     * @var Array
     */
    protected $businesses;

    /**
     * constructor.
     */
    public function __construct()
    {

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
    public function betweenDates( $start, $end )
    {
        $this->start_date = Carbon::parse( $start )->format( 'Y-m-d 00:00:00' );
        $this->end_date   = Carbon::parse( $end )->format( 'Y-m-d 23:59:59' );

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {

        $results = OccAccDeductible::whereHas( 'caregiver', function( $q ){

            $q->forRequestedBusinesses();
        })
        ->whereBetween( 'created_at', [

            $this->start_date,
            $this->end_date
        ])
        ->get()
        ->map( function( $deductible ){

            $caregiver = Caregiver::find( $deductible->caregiver_id );
            $chain_name = $caregiver->businessChains->first()->name;
            $deductible->chain_name = $chain_name;
            return $deductible;
        });

        return $results;
    }
}
