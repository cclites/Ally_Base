<?php

namespace App\Reports;

use App\Shift;
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
     * constructor.
     */
    public function __construct()
    {
        $this->query = DB::table( 'shifts' )
            ->leftJoin( 'businesses as business', 'shifts.business_id', '=', 'business.id' )
            ->leftJoin( 'caregivers as caregiver', 'shifts.caregiver_id', '=', 'caregiver.id' )
            ->leftJoin( 'users as user', 'shifts.caregiver_id', '=', 'user.id' )
            ->select([
                "user.id as user_id",
                DB::raw( "CONCAT(user.firstname, ' ', user.lastname) as caregiver_name" ),
                "business.name as registry",
                "business.id as registry_id",
                "business.shift_rounding_method as rounding_method",
                DB::raw( "SEC_TO_TIME(SUM(TO_SECONDS(shifts.checked_out_time) - TO_SECONDS(shifts.checked_in_time))) AS duration" ),
            ])
            ->groupBy([ 'user_id', 'registry', 'registry_id', 'rounding_method' ])
            ->where( 'caregiver.has_occ_acc', '1' )
            ->whereNotNull( 'shifts.checked_out_time' );
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
        $this->start_date = Carbon::parse( $end )->subWeek()->format( 'Y-m-d 00:00:00' );
        $this->end_date   = Carbon::parse( $end )->format( 'Y-m-d 23:59:59' );

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
     * Set filter for businesses.
     *
     * @param $id
     * @return $this
     */
    public function forTheFollowingBusinesses( $ids )
    {
        $this->businesses = $ids ?? auth()->user()->role->businesses->pluck( 'id' );

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $query = $this->query()
            ->whereBetween( 'checked_in_time', [ $this->start_date, $this->end_date ]);

        if( $this->businesses ) $query->whereIn( 'business.id', [ $this->businesses ]);

        $deduction = config( 'ally.occ_acc_deductible' );

        $results = $query->get();

        $results->map( function ( $shift ) use ( $deduction ){

                $time_worked = Carbon::createFromFormat( 'H:i:s', $shift->duration );

                // apply the respective registry's rounding method..
                switch( $shift->rounding_method ){

                    case 'individual':
                        // round the minutes to the nearest quarter-hour first, then convert to hours rounding to 2 decimals

                        $shift->duration = add( $time_worked->hour, divide( multiply( floor( multiply( divide( $time_worked->minute, 60 ), 4 ) ), 15 ), 60 ) );
                        break;
                    case 'none':
                        // no special rounding, do nothing

                        $shift->duration = add( $time_worked->hour, divide( $time_worked->minute, 60 ) );
                        break;
                    case 'shift': // the db default value
                    default:
                        // shift rounding, rounds it to 0.25 so use the round_to_fraction method with default values

                        $shift->duration = round_to_fraction( add( $time_worked->hour, divide( $time_worked->minute, 60 ) ) );
                        break;
                }
                // individual rounding
                // no rounding

                // return the minimum betweeen 9.00 and ( duration * deduction )
                $shift->deduction = min( 9.00, multiply( $deduction, $shift->duration ) );
                $shift->selected = 0;

                return $shift;
            });

        return $results;
    }
}
