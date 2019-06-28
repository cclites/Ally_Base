<?php


namespace App\Reports;


use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;

class PayerInvoiceReport extends BaseReport
{

    protected $query;

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */

    public function __construct()
    {
        $this->query = ClientInvoiceQuery::query();
    }

    public function query()
    {
        return $this->query;
    }

    public function forDates($startDate, $endDate){

    }

    public function forPayer(){

    }

    /**
     * @return Collection
     */
    protected function results() : ?iterable
    {
        $query = clone $this->query;

        // TODO: Implement results() method.
        $invoices = $query->groupBy('client_id')->get()
                    ->map(function (ClientInvoice $item) {
                        $item->billable = $item->getItems();
                        return $item;
                    });

        return $invoices;
    }

}