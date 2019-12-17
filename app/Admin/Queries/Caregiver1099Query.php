<?php

namespace App\Admin\Queries;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Admin\Queries\BaseQuery;

class Caregiver1099Query
{
    protected $filters = [];
    protected $threshold = 600;

    /**
     * query is a function call to serve as a wrapper to make this
     * appear similar to operation to a normal query object.
     *
     * @param array $filters
     * @return array
     */
    public function generateReport(array $filters): array
    {
        $this->setFilters($filters);
        return $this->generateQuery();
    }

    function generateQuery()
    {
        \DB::statement('set session sql_mode=\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\';');

        // IMPORTANT NOTE
        // The 1099 query needs to stay consistent year to year, we need to use the client payment date as the basis for inclusion in the tax year.

        $query = \DB::table('clients as c')
            ->selectRaw("c.id as client_id,
                u1.firstname as client_fname,
                u1.lastname as client_lname,
                u1.email as client_email,
                c.client_type,
                c.ssn as client_ssn,
                c.caregiver_1099,
                a1.address1 as client_address1,
                a1.address2 as client_address2,
                a1.city as client_city,
                a1.state as client_state,
                a1.zip as client_zip,
                b.id as business_id,
                b.name as business_name,
                u2.id as caregiver_id,
                u2.firstname as caregiver_fname,
                u2.lastname as caregiver_lname,
                u2.email as caregiver_email,
                c2.ssn as caregiver_ssn,
                c2.uses_ein_number,
                a2.address1 as caregiver_address1,
                a2.address2 as caregiver_address2,
                a2.city as caregiver_city,
                a2.state as caregiver_state,
                a2.zip as caregiver_zip,
                ct.id as caregiver_1099_id,
                ct.payment_total as caregiver_1099_amount,
                ct.business_id as caregiver_1099_location,
                SUM(h.caregiver_shift) as payment_total
            ")
            ->join('shifts as s', 's.client_id', '=', 'c.id')
            ->join('payments as p', 's.payment_id', '=', 'p.id')
            ->join('shift_cost_history as h', 's.id', '=', 'h.id')
            ->join('users as u1', 's.client_id', '=', 'u1.id')
            ->join('users as u2', 's.caregiver_id', '=', 'u2.id')
            ->join('caregivers as c2', 'u2.id', '=', 'c2.id')
            ->join('businesses as b', 'c.business_id', '=', 'b.id')
            ->leftJoin('caregiver_1099s as ct', function ($join) {
                $join->on('c.id', '=', 'ct.client_id')
                    ->on('c2.id', '=', 'ct.caregiver_id');
            })
            ->joinSub(\DB::table('addresses'), 'a1', 'a1.user_id', '=', 'u1.id', 'left')
            ->joinSub(\DB::table('addresses'), 'a2', 'a2.user_id', '=', 'u2.id', 'left')
            ->whereBetween('p.created_at', [
                Carbon::parse($this->filters['year']['value'] . '-01-01 00:00:00'),
                Carbon::parse($this->filters['year']['value'] . '-12-31 23:59:59')
            ])
            ->where('c.business_id', $this->filters['business_id']['value'])
            ->where('c.send_1099', 'yes');

        if (array_key_exists('client_id', $this->filters) && filled($this->filters['client_id']['value'])) {
            $query->where('u1.id', '=', $this->filters['client_id']['value']);
        }

        if (array_key_exists('caregiver_id', $this->filters) && filled($this->filters['caregiver_id']['value'])) {
            $query->where('c2.id', '=', $this->filters['caregiver_id']['value']);
        }

        if (array_key_exists('caregiver_1099', $this->filters) && filled($this->filters['caregiver_1099']['value'])) {
            if ($this->filters['caregiver_1099']['value'] && $this->filters['caregiver_1099']['value'] !== 'no') {
                $query->where('c.caregiver_1099', '=', (string)$this->filters['caregiver_1099']['value']);
            } elseif ($this->filters['caregiver_1099']['value'] && $this->filters['caregiver_1099']['value'] === 'no') {
                $query->whereNull('c.caregiver_1099');
            }
        }

        if (array_key_exists('transmitted', $this->filters) && filled($this->filters['transmitted']['value'])) {
            if ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value'] === 1) {
                $query->whereNotNull('ct.transmitted_at');
            } elseif ($this->filters['transmitted']['value'] && $this->filters['transmitted']['value'] === 0) {
                // TODO: check if this if statement is correct
                $query->whereNull('ct.transmitted_at');
            }
        }

        if (array_key_exists('created', $this->filters) && filled($this->filters['created']['value'])) {
            if ($this->filters['created']['value'] && $this->filters['created']['value'] === 1) {
                $query->whereNotNull('ct.id');
            } elseif ($this->filters['created']['value'] && $this->filters['created']['value'] === 0) {
                $query->whereNull('ct.id');
            }
        }

        $query->groupBy('s.client_id', 's.caregiver_id')
            ->having('payment_total', '>=', $this->threshold);

        return $query->get()->toArray();
    }

    /**
     * Set filters for query
     *
     * @param array|$filters
     * @return Model|void
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter => $value) {
            $this->filters[$filter] = ['name' => $filter, 'value' => $value];
        }
    }
}