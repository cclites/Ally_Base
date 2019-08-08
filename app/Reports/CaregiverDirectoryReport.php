<?php
namespace App\Reports;

use App\Caregiver;
use App\Traits\IsDirectoryReport;
use App\CustomField;
use Carbon\Carbon;

class CaregiverDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;

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
     * Filter by active status.
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

        if( $this->start_date ) $this->query()->whereHas( 'user', function( $query ){ $query->where( 'users.created_at', '>=', ( new Carbon( $this->start_date . ' 00:00:00', 'America/New_York' ) )->setTimezone( 'UTC' ) ); } );
        if( $this->end_date ) $this->query()->whereHas( 'user', function( $query ){ $query->where( 'users.created_at', '<=', ( new Carbon( $this->end_date . ' 23:59:59', 'America/New_York' ) )->setTimezone( 'UTC' ) ); } );

        $caregivers = $this->query()->with( 'meta' )
            ->get()->map( function( $caregiver ){

                $caregiver->phone             = $caregiver->user->notification_phone;
                $caregiver->emergency_contact = $caregiver->user->emergency_contact ? $caregiver->user->formatEmergencyContact() : '-';
                $caregiver->referral          = $caregiver->referralSource ? $caregiver->referralSource->name : '-';
                $caregiver->certification     = $caregiver->certification ? $caregiver->certification : '-';
                $caregiver->smoking_okay      = $caregiver->smoking_okay ? "Yes" : "No";
                $caregiver->ethnicity         = $caregiver->ethnicity ? $caregiver->ethnicity : '-';
                $caregiver->medicaid_id       = $caregiver->medicaid_id ? $caregiver->medicaid_id : '-';
                $caregiver->gender            = $caregiver->user->gender ? $caregiver->user->gender : '-';

                return $caregiver;
            });

        return $caregivers;






        // Erik 8/8/19 =>
        // this is the old function code that was here. Again, as far as I can tell this wasn't even in use. Leaving it here just in case it is needed.. mainly for archive purposes

        // $this->generated = true;
        // $customFields =CustomField::forAuthorizedChain()->where('user_type', 'caregiver')->get();
        // $rows = $caregivers->map(function(Caregiver $caregiver) use(&$customFields) {
        //     $result = [
        //         'id' => $caregiver->id,
        //         'firstname' => $caregiver->user->firstname ? $caregiver->user->firstname : '-',
        //         'lastname' => $caregiver->user->lastname ? $caregiver->user->lastname : '-',
        //         'username' => $caregiver->username ? $caregiver->username : '-',
        //         'title' => $caregiver->title,
        //         'certification' => $caregiver->certification ? $caregiver->certification : '-',
        //         'gender' => $caregiver->user->gender ? $caregiver->user->gender : '-',
        //         'orientation_date' => $caregiver->orientation_date ? $caregiver->orientation_date->format('m-d-Y') : '-',
        //         'smoking_okay' => $caregiver->smoking_okay ? "Yes" : "No",
        //         'ethnicity' =>$caregiver->ethnicity ? $caregiver->ethnicity : '-',
        //         'application_date' =>$caregiver->application_date ? $caregiver->application_date->format('m-d-Y') : '-',
        //         'medicaid_id' => $caregiver->medicaid_id ? $caregiver->medicaid_id : '-',
        //         'email' => $caregiver->user->email,
        //         'phone' => $caregiver->user->notification_phone,
        //         'active' => $caregiver->active ? 'Active' : 'Inactive',
        //         'address' => $caregiver->address ? $caregiver->address->full_address : '',
        //         'emergency_contact' => $caregiver->user->formatEmergencyContact(),
        //         'date_added' => $caregiver->user->created_at->format('m-d-Y'),
        //         'referral' => $caregiver->referralSource ? $caregiver->referralSource->name : ''
        //     ];

        //     // Add the custom fields to the report row
        //     foreach($customFields as $field) {
        //         if($meta = $caregiver->meta->where('key', $field->key)->first()) {
        //             $result[$field->key] = $meta->display();
        //             continue;
        //         }

        //         $result[$field->key] = $field->default;
        //     }

        //     return $result;
        // });

        // $rows = $this->filterColumns($rows);
        // return $rows;
    }
}
