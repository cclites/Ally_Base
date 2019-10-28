<?php

namespace App\Reports;

use App\Billing\Deposit;
use App\Billing\Payment;
use App\Business;
use App\BusinessChain;

class ChargesVsDepositsReport extends BaseReport
{
    /**
     * Date range filter.
     *
     * @var array
     */
    protected $range = [];

    /**
     * ChargesVsDepositsReport constructor.
     */
    public function __construct()
    {
        $this->query = BusinessChain::query();
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
     * Set filters for the report.
     *
     * @param array $dateRange
     */
    public function applyFilters(array $dateRange): void
    {
        $this->range = $dateRange;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results(): ?iterable
    {
        return BusinessChain::get()->map(function (BusinessChain $chain) {
            $businesses = $chain->businesses()->pluck('id');
            $caregivers = $chain->caregivers()->pluck('caregiver_id');
            $clients = $chain->businesses->map(function (Business $business) {
                return $business->clients()->pluck('id');
            })->flatten(1);

            $charges = Payment::whereBetween('created_at', $this->range)
                ->where(function ($q) use ($businesses, $clients) {
                    return $q->whereIn('business_id', $businesses)
                        ->orWhereIn('client_id', $clients);
                })
                ->get()
                ->reduce(function (float $carry, Payment $payment) {
                    return add($carry, floatval($payment->amount));
                }, floatval(0));

            $deposits = Deposit::whereBetween('created_at', $this->range)
                ->where(function ($q) use ($businesses, $caregivers) {
                    return $q->whereIn('business_id', $businesses)
                        ->orWhereIn('caregiver_id', $caregivers);
                })
                ->get()
                ->reduce(function (float $carry, Deposit $deposit) {
                    return add($carry, floatval($deposit->amount));
                }, floatval(0));

            return [
                'chain' => $chain->name,
                'chain_id' => $chain->id,
                'charges' => $charges,
                'deposits' => $deposits,
                'diff' => abs(subtract($charges, $deposits)),
                'diff_percent' => $this->getDiff($charges, $deposits),
            ];
        });
    }

    /**
     * Get percentage difference between charges and deposits.
     *
     * @param float $charges
     * @param float $deposits
     * @return float
     */
    public function getDiff(float $charges, float $deposits) : float
    {
        if ($charges == 0 || $deposits == 0) {
            return add($charges, $deposits);
        }

        if ($charges > $deposits) {
            $more = $charges;
            $less = $deposits;
        } else {
            $more = $deposits;
            $less = $charges;
        }

        return (($more - $less) / ($more)) * 100;
    }
}
