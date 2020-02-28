<?php

namespace App\Reports;

use App\Caregiver;
use App\Shift;
use App\Shifts\DurationCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * The businesses IDs.
     *
     * @var Array
     */
    protected $businesses;

    /**
     * The relevant caregivers
     *
     */
    protected $caregivers;

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
    public function forWeekStartingAt( $start )
    {
        $this->start_date = Carbon::parse( $start )->format( 'Y-m-d 00:00:00' );
        $this->end_date   = Carbon::parse( $start )->addDays( 6 )->format( 'Y-m-d 23:59:59' );

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
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $deduction = config( 'ally.occ_acc_deductible' );

        // grab all shifts with caregiver info and business info
        // scope to businesses and time frame and caregiver with occacc
        // go through each shift and calculate duration
        $results = Shift::forRequestedBusinesses()
            ->whereHasntBeenUsedForOccAccDeductible()
            ->whereConfirmed()
            ->whereBetween( 'checked_in_time', [ $this->start_date, $this->end_date ])
            ->whereNotNull( 'checked_out_time' )
            ->with([ 'business', 'caregiver' ])
            ->whereHas( 'caregiver', function( $q ){

                return $q->where( 'has_occ_acc', '1' );
            })->get()
            ->groupBy( 'caregiver_id' )
            ->map( function( $shift_aggregate ) use ( $deduction ) {

                $duration       = 0;
                $registry_names = [];
                $registry_ids   = [];
                foreach( $shift_aggregate as $shift ){

                    $user_id        = $shift->caregiver_id;
                    $caregiver_name = $shift->caregiver->nameLastFirst;

                    if( !in_array( $shift->business->name, $registry_names ) ) array_push( $registry_names, $shift->business->name );
                    if( !in_array( $shift->business->id, $registry_ids ) ) array_push( $registry_ids, $shift->business->id );

                    // this will automatically take the rounding method and aggregate the durations properly
                    $duration += $shift->duration();
                }

                return [

                    'user_id'        => $user_id,
                    'caregiver_name' => $caregiver_name,
                    'registry'       => implode( ', ', $registry_names ),
                    'registry_id'    => implode( ', ', $registry_ids ),
                    'duration'       => $duration,
                    'deduction'      => min( 9.00, multiply( $duration, $deduction ) )
                ];
            });

        return $results;
    }
}
