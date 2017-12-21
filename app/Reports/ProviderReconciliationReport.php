<?php
namespace App\Reports;

use App\Business;
use DB;

class ProviderReconciliationReport extends BaseReport
{
    protected $query;
    protected $business;

    public function __construct(Business $business)
    {
        $this->business = $business;

        $deposits = DB::table('deposits')->join('gateway_transactions', 'gateway_transactions.id', '=', 'deposits.transaction_id')
            ->selectRaw("gateway_transactions.id, deposits.amount as amount_deposited,'0' as amount_withdrawn, gateway_transactions.created_at")
            ->where('business_id', $business->id)
            ->whereNull('caregiver_id');
        $payments = DB::table('payments')->join('gateway_transactions', 'gateway_transactions.id', '=', 'payments.transaction_id')
            ->selectRaw("gateway_transactions.id, '0' as amount_deposited, payments.amount as amount_withdrawn, gateway_transactions.created_at")
            ->where('business_id', $business->id)
            ->whereNull('client_id');
        $this->query = $deposits->union($payments);
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
}