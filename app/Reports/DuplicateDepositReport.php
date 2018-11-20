<?php

namespace App\Reports;

use App\Shift;

/**
 * Class DuplicateDepositReport
 * Return all the shifts that reside in multiple successful deposits of the same type
 *
 *
 * @package App\Reports
 */
class DuplicateDepositReport extends BusinessResourceReport
{

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        if (!$this->query) {
            $this->query = Shift::with(['client', 'caregiver', 'business', 'deposits'])
                                ->whereHas('deposits', function($q) {
                                    $q->select('deposit_type')
                                      ->where('success', 1)
                                      ->groupBy('deposit_type')
                                      ->havingRaw('count(*) > 1');
                                });
        }
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        return $this->query()->get()->map(function(Shift $shift) {
            $shift->caregiver_total = $shift->costs()->getCaregiverCost();
            $shift->provider_total = $shift->costs()->getProviderFee();
            $shift->total_cost = $shift->costs()->getTotalCost();
            return $shift;
        });
    }
}
