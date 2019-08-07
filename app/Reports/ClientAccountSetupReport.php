<?php

namespace App\Reports;

use App\Client;
use App\PhoneNumber;

class ClientAccountSetupReport extends BaseReport
{
    private $phoneFilter;
    private $statusFilter;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Client::with( 'user', 'user.phoneNumbers' );
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
     * Filter by account status.
     *
     * @param $status
     * @return ClientAccountSetupReport
     */
    public function setStatusFilter( $status ) : self
    {
        $this->statusFilter = $status;

        return $this;
    }

    /**
     * Filter by phone type.
     *
     * @param $phone
     * @return ClientAccountSetupReport
     */
    public function setPhoneFilter( $phone ) : self
    {
        $this->phoneFilter = $phone;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     * 
     * whereDoesntHave and doesntHave are not supported on polymorphic relationships in Laravel,
     * so using the local keys to check is necessary
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        switch ( $this->statusFilter ) {

            case 'active_no_payment':

                // i'd like to get a little bit better clarification on what it means to 'not have payment'.. these are the candidate columns I could find.. are their related 'type' columns included? is 'client_payer' relationship included?
                $this->query()->active()->where( 'default_payment_id', null )->where( 'medicaid_plan_id', null )->where( 'medicaid_payer_id', null );
                break;
            case 'inactive_no_payment':

                $this->query()->inactive()->where( 'default_payment_id', null )->where( 'medicaid_plan_id', null )->where( 'medicaid_payer_id', null );
                break;
            case 'scheduled_no_payment':

                // i see the word 'scheduled' and think it has to refer to 'future schedules'. There is also a relation to ask for any schedules including those in the past
                $this->query()->active()->whereHas( 'futureSchedules' )->where( 'default_payment_id', null )->where( 'medicaid_plan_id', null )->where( 'medicaid_payer_id', null );
                break;
            default:

                $this->query()->active();
                break;
        }

        $data = $this->query()
            ->get()
            ->map( function ( Client $item ) {

                if ( empty( $item->user->setup_status ) ) {

                    $status = 'Not Started';
                } else if ( in_array( $item->user->setup_status, [ Client::SETUP_CREATED_ACCOUNT, Client::SETUP_ACCEPTED_TERMS ] ) ) {

                    $status = 'In Progress';
                } else if ( $item->user->setup_status == Client::SETUP_ADDED_PAYMENT ) {

                    $status = 'Complete';
                }

                return [

                    'id'           => $item->id,
                    'name'         => $item->nameLastFirst,
                    'email'        => $item->user->email,
                    'mobile_phone' => optional( $item->user->phoneNumbers->where( 'receives_sms', 1 )->first() )->number ?? '',
                    'home_phone'   => optional( $item->user->phoneNumbers->where( 'receives_sms', 0 )->first() )->number ?? '',
                    'setup_status' => $status,
                ];
            })
            ->sortBy( 'name' )
            ->values();

        switch ( $this->phoneFilter ) {

            case 'has_mobile':

                $data = $data->filter(function ($row) {

                    return filled( $row[ 'mobile_phone' ] );
                });
                break;
            case 'any':

                $data = $data->filter( function ( $row ) {

                    return filled( $row[ 'mobile_phone' ] ) || filled( $row[ 'home_phone' ] );
                });
                break;
            case 'none':

                $data = $data->filter(function ( $row ) {

                    return empty( $row[ 'mobile_phone' ] ) && empty( $row[ 'home_phone' ] );
                });
                break;
        }

        return $data->values();
    }
}
