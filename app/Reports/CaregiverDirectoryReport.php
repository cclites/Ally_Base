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
     * @var array
     */
    protected $columns;

    /**
     * CaregiverDirectoryReport constructor.
     */
    public function __construct()
    {
        $this->query = Caregiver::with(['user', 'address']);
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
                'firstname' => $caregiver->user->firstname,
                'lastname' => $caregiver->user->lastname,
                'email' => $caregiver->user->email,
                'active' => $caregiver->active ? 'Active' : 'Inactive',
                'address' => $caregiver->address ? $caregiver->address->full_address : '',
                'date_added' => $caregiver->user->created_at->format('m-d-Y'),
            ];

            // Add the custom fields to the report row
            foreach($customFields as $field) {
                if($meta = $caregiver->meta->where('key', $field->key)->first()) {
                    $result[$field->key] = $meta->display();
                    continue;
                }

                $result[$field->key] = $field->default();
            }

            return $result;
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
