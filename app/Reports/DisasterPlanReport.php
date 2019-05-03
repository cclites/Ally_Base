<?php

namespace App\Reports;

use App\Client;
use App\DisasterCode;

class DisasterPlanReport extends BaseReport
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Client::with(['user', 'address', 'contacts', 'phoneNumbers', 'business'])
            ->forRequestedBusinesses();
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
     * Filter by client ID.
     *
     * @param string|null $clientId
     * @return DisasterPlanReport
     */
    public function forClient(?string $clientId = null) : self
    {
        if (! empty($clientId)) {
            $this->query->where('id', $clientId);
        }

        return $this;
    }

    /**
     * Filter by client status.
     *
     * @param string|null $status
     * @return DisasterPlanReport
     */
    public function withStatus(?string $status = null) : self
    {
        if ($status == 'active') {
            $this->query->whereHas('user', function($q) {
                $q->where('active', 1);
            });
        } else if ($status == 'inactive') {
            $this->query->whereHas('user', function($q) {
                $q->where('active', 0);
            });
        }

        return $this;
    }

    /**
     * Filter by disaster plan code.
     *
     * @param DisasterCode|null $code
     * @return DisasterPlanReport
     */
    public function forDisasterCode(?DisasterCode $code = null) : self
    {
        if (! empty($code)) {
            $this->query->where('disaster_code_plan', $code);
        }

        return $this;
    }

    /**
     * Filter by zipcode.
     *
     * @param string|null $zipcode
     * @return DisasterPlanReport
     */
    public function forZipcode(?string $zipcode = null) : self
    {
        if (! empty($zipcode)) {
            $this->query->whereHas('address', function ($q) use ($zipcode) {
                $q->where('zip', $zipcode);
            });
        }

        return $this;
    }

    /**
     * Filter by business.
     *
     * @param string|null $businessId
     * @return DisasterPlanReport
     */
    public function forBusiness(?string $businessId = null) : self
    {
        if (! empty($businessId)) {
            $this->query->where('business_id', $businessId);
        }

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        return $this->query()
            ->get()
            ->map(function (Client $row) {
                $emergencyContact = $row->contacts->where('is_emergency', true)->sortBy('emergency_priority')->first();

                return [
                    'id' => $row->id,
                    'Office Location' => $row->business->name,
                    'Client' => $row->name,
                    'Client Status' => $row->active == 1 ? 'Active' : 'Inactive',
                    'Disaster Code Plan' => $row->disaster_code_plan,
                    'Disaster Planning Description' => $row->disaster_planning,
                    'Client Address' => optional($row->address)->address1,
                    'City' => optional($row->address)->city,
                    'Zipcode' => optional($row->address)->zip,
                    'Client Phone 1' => $row->getPhoneNumberByType('primary'),
                    'Client Phone 2' => $row->getPhoneNumberByType('billing'),
                    'Emergency Contact' => optional($emergencyContact)->name,
                    'Emergency Contact Phone' => optional($emergencyContact)->phone1,
                ];
            });
    }
}
