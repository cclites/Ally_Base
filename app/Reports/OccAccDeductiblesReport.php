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
                DB::raw( "SEC_TO_TIME(SUM(TO_SECONDS(shifts.checked_out_time) - TO_SECONDS(shifts.checked_in_time))) AS duration" ),
            ])
            ->groupBy([ 'user_id', 'registry' ])
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
        $this->businesses = $ids;

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

        $results = $query->get()
            ->map( function ( $shift ) use ( $deduction ){

                $time_worked = Carbon::createFromFormat( 'H:i:s', $shift->duration );
                $duration = $time_worked->hour + ( round( $time_worked->minute / 60, 2 ) );

                // return the minimum betweeen 9.00 and ( duration * deduction )
                $shift->deduction = min( 9.00, round( $deduction * $duration, 2 ) );
                $shift->selected = 0;

                return $shift;
            });

        return $results;
    }
}
