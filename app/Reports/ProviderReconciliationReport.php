<?php
namespace App\Reports;

use App\Business;
use App\Contracts\BusinessReportInterface;
use App\User;
use DB;

class ProviderReconciliationReport extends BaseReport implements BusinessReportInterface
{
    protected $query;
    protected $business;

    public function __construct()
    {
        $this->query = DB::table('view_business_reconciliation');
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
        return $this->query()->get();
    }

    public function forBusinesses(array $businessIds = null)
    {
        $this->query()->whereIn('business_id', (array) $businessIds);

        return $this;
    }

    public function forRequestedBusinesses(array $businessIds = null, User $authorizedUser = null, $dates = null, $types = [])
    {
        if ($types === null) {
            $types = [];
        }

        if ($businessIds === null) $businessIds = array_filter((array) request()->input('businesses', []));
        if ($authorizedUser === null) $authorizedUser = auth()->user();

        $businessIds = $authorizedUser->filterAttachedBusinesses($businessIds);
        if (!count($businessIds)) $businessIds = $authorizedUser->getBusinessIds();

        $this->query()->whereIn('business_id', (array) $businessIds);
        
        if ($dates !== null) {
            $this->query()->whereBetween('created_at', $dates->values()->toArray());
        }
        if (in_array('deposits', $types)) {
            $this->query()->where('amount_deposited', '!=', 0);
        }
        if (in_array('withdrawls', $types)) {
            $this->query()->where('amount_withdrawn', '!=', 0);
        }

        return $this;
    }
}