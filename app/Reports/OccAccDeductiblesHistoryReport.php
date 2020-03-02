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
        ->with([ 'shifts', 'caregiver', 'caregiver.phoneNumber', 'caregiver.address' ])
        ->whereBetween( 'created_at', [

            $this->start_date,
            $this->end_date
        ])
        ->get()
        ->map( function( $deductible ){

            // $chain_name = $deductible->caregiver->businessChains->first()->name; may be needed.. not sure.. i suspect it will be asked for
            // $deductible->chain_name = $chain_name;

            return [

                'id'                 => $deductible->id,
                'first_name'         => $deductible->caregiver->firstname,
                'last_name'          => $deductible->caregiver->lastname,
                'dob'                => $deductible->caregiver->date_of_birth,
                'address'            => $deductible->caregiver->address->street_address,
                'city'               => $deductible->caregiver->address->city,
                'state'              => $deductible->caregiver->address->state,
                'zip'                => $deductible->caregiver->address->zip,
                'phone_number'       => $deductible->caregiver->phoneNumber,
                'email'              => $deductible->caregiver->email,
                'hours_worked'       => $deductible->shifts->sum( 'duration' ),
                'deduction_amount'   => $deductible->amount,
                'certificate_number' => $deductible->caregiver->certificate_number
            ];
        });

        return $results;
    }
}
