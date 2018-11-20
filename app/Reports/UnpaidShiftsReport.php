<?php

namespace App\Reports;

use App\Shift;

class UnpaidShiftsReport extends BusinessResourceReport
{

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        if (!$this->query) {
            $this->query = Shift::with(['client', 'caregiver', 'business'])
                                ->whereIn('status', [
                                    Shift::PAID_BUSINESS_ONLY,
                                    Shift::PAID_CAREGIVER_ONLY,
                                    Shift::PAID_NOT_CHARGED,
                                    Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
                                    Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
                                ]);
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
