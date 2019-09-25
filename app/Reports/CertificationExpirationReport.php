<?php

namespace App\Reports;

use App\CaregiverLicense;
use App\Contracts\BusinessReportInterface;
use App\User;
use Carbon\Carbon;

class CertificationExpirationReport extends BaseReport implements BusinessReportInterface
{
    protected $caregiverId;
    protected $activeOnly = false;
    protected $inactiveOnly = false;
    protected $expiration_type;
    protected $all_expiration_types;
    protected $showExpired;
    protected $showEmptyExpirations;
    protected $days;
    protected $startDate;
    protected $endDate;
    protected $showScheduled;

    public function setCaregiver(?int $id) : self
    {
        $this->caregiverId = $id;
        return $this;
    }

    public function setBetweenDates( $startDate, $endDate ) : self
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        return $this;
    }

    public function setShowScheduled(?bool $showScheduled) : self
    {
        $this->showScheduled = $showScheduled;
        return $this;
    }

    public function setShowEmptyExpirations(?bool $showEmptyExpirations) : self
    {
        $this->showEmptyExpirations = $showEmptyExpirations;
        return $this;
    }

    public function setActiveOnly(?bool $activeOnly) : self
    {
        $this->activeOnly = $activeOnly;
        return $this;
    }

    public function setInactiveOnly(?bool $inactiveOnly) : self
    {
        $this->inactiveOnly = $inactiveOnly;
        return $this;
    }

    public function setAllTypes( ?object $expiration_types ) : self
    {
        $this->all_expiration_types = $expiration_types;
        return $this;
    }

    public function setExpirationType(?int $expiration_type) : self
    {
        $this->expiration_type = $expiration_type;
        return $this;
    }

    public function setExpired(?bool $showExpired) : self
    {
        $this->showExpired = $showExpired;
        return $this;
    }

    public function setDays(?int $days) : self
    {
        $this->days = $days;
        return $this;
    }

    /**
     * ScheduledPaymentsReport constructor.
     */
    public function __construct()
    {
        $this->query = CaregiverLicense::with('caregiver', 'caregiver.address', 'caregiver.schedules' );
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
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $query = $this->query()->whereHas('caregiver', function ($q) {
            if ($this->activeOnly) {
                $q->active();
            } else if ($this->inactiveOnly) {
                $q->inactive();
            }
        });

        if ( $this->caregiverId ) {

            $query->where('caregiver_id', $this->caregiverId);
        }

        if ( $this->startDate ) {

            $query->where( 'expires_at', '>=', Carbon::parse( $this->startDate )->format( 'Y-m-d' ) );
        }

        if ( $this->endDate ) {

            $query->where( 'expires_at', '<=', Carbon::parse( $this->endDate )->format( 'Y-m-d' ) );
        }

        if ( isset( $this->expiration_type ) ) {

            $by_caregivers = $query->where( 'chain_expiration_type_id', $this->expiration_type )->get()->groupBy( 'caregiver_id' );
        } else {
            // if no expiration type is specified, map all to the result set

            $by_caregivers = $query->get()->groupBy( 'caregiver_id' );

            if( $this->showEmptyExpirations ){

                foreach( $this->all_expiration_types as $type ){
                    // for every type of expiration that the chain has..

                    foreach( $by_caregivers as $caregiver_id => $caregiver ){
                        // make sure it is represented for every caregiver returned, blank or not..

                        // This step is a little messy, but necessary for the following reason:
                        //  - I realized there is a bug where existing licenses OUTSIDE of the date range would be picked up as non-exsisting because of this mapping and displayed as such
                        $allLicenses = CaregiverLicense::where( 'caregiver_id', $caregiver->first()->caregiver_id )->get();

                        if( !$caregiver->where( 'chain_expiration_type_id', '=', $type->id )->first() && !$allLicenses->where( 'chain_expiration_type_id', '=', $type->id )->first() ){
                            // if the expiration type is not found for this caregiver, add a blank row

                            $caregiver->push( CaregiverLicense::make([

                                'id'                       => null,
                                'caregiver_id'             => $caregiver_id,
                                'name'                     => $type->type,
                                'expires_at'               => null,
                                'chain_expiration_type_id' => $type->id
                            ]));
                        }
                    }
                }
            }
        }

        if( $this->showScheduled ){
            // if flagged, remove the caregivers who do not have future schedules. This may be better to do on the SQL side with extra joins and relationship querying.
            // I figured it would dramatically alter the query for one specific case and be a lot more work than needed, let me know if this is okay

            foreach( $by_caregivers as $index => $caregiver ){

                if( $caregiver->first()->caregiver->futureSchedules->count() == 0 ){

                    $by_caregivers->forget( $index );
                };
            }
        }

        return $by_caregivers->flatten()->map(function (CaregiverLicense $license) {

            return [

                'id'                 => $license->id,
                'name'               => $license->name,
                'expiration_date'    => $license->expires_at ? ( new Carbon( $license->expires_at ) )->format( 'Y-m-d' ) : null,
                'caregiver_id'       => $license->caregiver->id,
                'caregiver_name'     => $license->caregiver->nameLastFirst(),
                'caregiver_active'   => $license->caregiver->active,
                'expiration_type_id' => $license->chain_expiration_type_id,
            ];
        });
    }

    public function forBusinesses(array $businessIds = null)
    {
        $this->query()->whereHas('caregiver', function($q) use ($businessIds) {
            $q->forBusinesses($businessIds);
        });

        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null)
    {
        $this->query()->whereHas('caregiver', function($q) use ($businessIds, $authorizedUser) {
            $q->forRequestedBusinesses($businessIds, $authorizedUser);
        });

        return $this;
    }
}