<?php
namespace App\Reports;

use App\Caregiver;
use App\Traits\IsDirectoryReport;
use App\CustomField;

class CaregiverDirectoryReport extends BusinessResourceReport
{
    use IsDirectoryReport;
    
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
        $this->query = Caregiver::with(['user', 'address', 'user.emergencyContacts', 'user.phoneNumbers']);
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $caregivers = $this->query->get();
        $this->generated = true;
        $customFields =CustomField::forAuthorizedChain()->where('user_type', 'caregiver')->get();
        $rows = $caregivers->map(function(Caregiver $caregiver) use(&$customFields) {
            $result = [
                'id' => $caregiver->id,
                'firstname' => $caregiver->user->firstname ? $caregiver->user->firstname : '-',
                'lastname' => $caregiver->user->lastname ? $caregiver->user->lastname : '-',
                'username' => $caregiver->username ? $caregiver->username : '-',
                'title' => $caregiver->title,
                'certification' => $caregiver->certification ? $caregiver->certification : '-',
                'gender' => $caregiver->user->gender ? $caregiver->user->gender : '-',
                'orientation_date' => $caregiver->orientation_date ? $caregiver->orientation_date->format('m-d-Y') : '-',
                'smoking_okay' => $caregiver->smoking_okay ? "Yes" : "No",
                'ethnicity' =>$caregiver->ethnicity ? $caregiver->ethnicity : '-',
                'application_date' =>$caregiver->application_date ? $caregiver->application_date->format('m-d-Y') : '-',
                'medicaid_id' => $caregiver->medicaid_id ? $caregiver->medicaid_id : '-',
                'email' => $caregiver->user->email,
                'phone' => $caregiver->user->notification_phone,
                'active' => $caregiver->active ? 'Active' : 'Inactive',
                'address' => $caregiver->address ? $caregiver->address->full_address : '',
                'emergency_contact' => $caregiver->user->formatEmergencyContact(),
                'date_added' => $caregiver->user->created_at->format('m-d-Y'),
                'referral' => $caregiver->referralSource ? $caregiver->referralSource->name : ''
            ];

            // Add the custom fields to the report row
            foreach($customFields as $field) {
                if($meta = $caregiver->meta->where('key', $field->key)->first()) {
                    $result[$field->key] = $meta->display();
                    continue;
                }

                $result[$field->key] = $field->default;
            }

            return $result;
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
