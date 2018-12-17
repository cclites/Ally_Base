<?php
namespace App\Reports;

use App\Caregiver;
use App\Traits\IsDirectoryReport;

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
        $rows = $caregivers->map(function(Caregiver $caregiver) {
            return [
                'id' => $caregiver->id,
                'firstname' => $caregiver->user->firstname,
                'lastname' => $caregiver->user->lastname,
                'email' => $caregiver->user->email,
                'active' => $caregiver->active ? 'Active' : 'Inactive',
                'address' => $caregiver->address ? $caregiver->address->full_address : '',
                'date_added' => $caregiver->user->created_at->format('m-d-Y'),
            ];
        });

        $rows = $this->filterColumns($rows);
        return $rows;
    }
}
