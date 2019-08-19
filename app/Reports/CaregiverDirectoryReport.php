<?php
namespace App\Reports;

use App\Caregiver;
use App\Traits\IsDirectoryReport;
use App\CustomField;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class CaregiverDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

    private $per_page = 10; // simple default for 'limit'
    private $current_page = 1; // simple default.. maybe it should start at zero?
    private $total_count;

    private $alias_filter;
    private $active_filter;

    private $start_date;
    private $end_date;

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * CaregiverDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::with([ 'user', 'address', 'user.emergencyContacts', 'user.phoneNumbers' ]);
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
     * Filter by active status.
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setActiveFilter( $active ) : self
    {
        $this->active_filter = $active;

        return $this;
    }

    /**
     * Filter by status alias.
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setStatusAliasFilter( $alias_id ) : self
    {
        $this->alias_filter = $alias_id;

        return $this;
    }

    /**
     * Set number of records to pagniate per page
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setPageCount( $count ) : self
    {
        $this->per_page = $count;

        return $this;
    }

    /**
     * Set current page
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setCurrentPage( $page ) : self
    {
        $this->current_page = $page;

        return $this;
    }

    /**
     * Filter by date
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setDateFilter( $start_date = null, $end_date = null ) : self
    {
        if( $start_date ) $this->start_date = $start_date;
        if( $end_date ) $this->end_date = $end_date;

        return $this;
    }

    /**
     * 
     * public accessor for the total count
     */
    public function getTotalCount()
    {
        return $this->total_count;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        switch( $this->active_filter ){

            case 'true':

                $this->query()->active();
                break;
            case 'false':

                $this->query()->inactive();
                break;
            default:

                break;
        }

        // date filters are not desired at this moment, kept for reference
        // if( $this->start_date ) $this->query()->whereHas( 'user', function( $query ){ $query->where( 'users.created_at', '>=', ( new Carbon( $this->start_date . ' 00:00:00', 'America/New_York' ) )->setTimezone( 'UTC' ) ); } );
        // if( $this->end_date ) $this->query()->whereHas( 'user', function( $query ){ $query->where( 'users.created_at', '<=', ( new Carbon( $this->end_date . ' 23:59:59', 'America/New_York' ) )->setTimezone( 'UTC' ) ); } );

        if( $this->alias_filter ) $this->query()->where( 'status_alias_id', $this->alias_filter );

        // perform count-query first
        $this->total_count = $this->query()->with( 'meta' )
            ->count();


        // implement pagination manually
        $this->query()->limit( $this->per_page )->offset( $this->per_page * ( $this->current_page - 1 ) );


        $caregivers = $this->query()->get();

        $caregivers->each( function( $caregiver ){

            // dd( $caregiver );
            $caregiver->title                    = $caregiver->title ?? '-';

            $caregiver->inactive_at              = $caregiver->inactive_at ?? '-';
            $caregiver->welcome_email_sent_at    = $caregiver->welcome_email_sent_at ?? '-';
            $caregiver->training_email_sent_at   = $caregiver->training_email_sent_at ?? '-';
            $caregiver->setup_status             = $caregiver->setup_status ?? '-';


            $caregiver->onboarded         = $caregiver->onboarded                ? $caregiver->onboarded : '';
            $caregiver->bank_account_id   = $caregiver->bank_account_id          ?? '-';
            $caregiver->phone             = $caregiver->user->notification_phone ?? '';
            $caregiver->emergency_contact = $caregiver->user->emergency_contact  ? $caregiver->user->formatEmergencyContact() : '-';
            $caregiver->referral          = $caregiver->referralSource           ? $caregiver->referralSource->name : '-';
            $caregiver->certification     = $caregiver->certification            ? $caregiver->certification : '-';
            $caregiver->smoking_okay      = $caregiver->smoking_okay             ? "Yes" : "No";
            $caregiver->ethnicity         = $caregiver->ethnicity                ? $caregiver->ethnicity : '-';
            $caregiver->medicaid_id       = $caregiver->medicaid_id              ? $caregiver->medicaid_id : '-';
            $caregiver->status_alias_id   = $caregiver->statusAlias              ? $caregiver->statusAlias->id : '-';
            $caregiver->status_alias_name = $caregiver->statusAlias              ? $caregiver->statusAlias->name : '-';
            $caregiver->gender            = $caregiver->user                     ? $caregiver->user->gender : '';

            return $caregiver;
        });

        // for quick verification, delete if not desired
        // dd( $caregivers->toArray() );

        return $caregivers;
    }
}
