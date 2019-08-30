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

    private $for_export;

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
     * set for export flag
     *
     * @param $status
     * @return CaregiverAccountSetupReport
     */
    public function setForExport( $flag ) : self
    {
        $this->for_export = $flag;

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
        if( !$this->for_export ){

            $this->query()->limit( $this->per_page )->offset( $this->per_page * ( $this->current_page - 1 ) );
        }


        $caregivers = $this->query()->get();

        $data = $caregivers->map( function( $caregiver ){

            return [

                'NameLastFirst'              => $caregiver->nameLastFirst                                              ?? '',
                'Certification'              => $caregiver->certification                                              ?? '',
                'Phone Number'               => $caregiver->notification_phone                                         ?? '',
                'Username'                   => $caregiver->username                                                   ?? '',
                'Email'                      => $caregiver->email                                                      ?? '',
                'Notification Email'         => $caregiver->notification_email                                         ?? '',
                'Date Of Birth'              => Carbon::parse( $caregiver->date_of_birth )->format( 'm/d/Y' )          ?? '',
                'Role Type'                  => $caregiver->role_type                                                  ?? '',
                'Onboarded'                  => Carbon::parse( $caregiver->onboarded )->format( 'm/d/Y' )              ?? '',
                'W9 Name'                    => $caregiver->w9_name                                                    ?? '',
                'Medicaid Id'                => $caregiver->medicaid_id                                                ?? '',
                'Gender'                     => $caregiver->gender                                                     ? $caregiver->formatted_gender : '',
                'Deactivation Note'          => $caregiver->deactivation_note                                          ?? '',
                'Smoking Okay'               => $caregiver->smoking_okay                                               ? 'Yes' : 'No',
                'Pets Dogs Okay'             => $caregiver->pets_dogs_okay                                             ? 'Yes' : 'No',
                'Pets Cats Okay'             => $caregiver->pets_cats_okay                                             ? 'Yes' : 'No',
                'Pets Birds Okay'            => $caregiver->pets_birds_okay                                            ? 'Yes' : 'No',
                'Ethnicity'                  => $caregiver->ethnicity                                                  ?? '',
                'Active'                     => $caregiver->active                                                     ? 'Active' : 'Inactive',
                'Inactive At'                => Carbon::parse( $caregiver->inactive_at )->format( 'm/d/Y' )            ?? '',
                'Welcome Email Sent'         => Carbon::parse( $caregiver->welcome_email_sent_at )->format( 'm/d/Y' )  ?? '',
                'Training Email Sent'        => Carbon::parse( $caregiver->training_email_sent_at )->format( 'm/d/Y' ) ?? '',
                'Setup Status'               => $caregiver->setup_status                                               ? title_case( str_replace( '_', ' ', $caregiver->setup_status ) ) : '',
                'Allow Sms Notifications'    => $caregiver->allow_sms_notifications                                    ? 'Yes' : 'No',
                'Allow Email Notifications'  => $caregiver->allow_email_notifications                                  ? 'Yes' : 'No',
                'Allow System Notifications' => $caregiver->allow_system_notifications                                 ? 'Yes' : 'No',
                'Emergency Contact'          => $caregiver->user->formatEmergencyContact()                             ?? '',
                'Referral'                   => $caregiver->referralSource                                             ? $caregiver->referralSource->organization : '',
                'Status Alias Name'          => $caregiver->statusAliasName                                            ?? '',
                'Masked Ssn'                 => $caregiver->masked_ssn                                                 ?? '',
            ];
        });

        return $data;
    }
}
