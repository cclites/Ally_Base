<?php


namespace App\Reports;


use App\Billing\ClientInvoice;
use App\Billing\Queries\ClientInvoiceQuery;
use Carbon\Carbon;

class PayerInvoiceReport extends BaseReport
{

    /**
     * @var object
     */
    protected $query;

    /**
     * @var int
     */
    protected $payer;

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

    public function forDates(string $start, string $end, ?string $timezone = null) : self
    {
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }

        $startDate = new Carbon($start . ' 00:00:00', $timezone);
        $endDate = new Carbon($end . ' 23:59:59', $timezone);
        $this->query->between($startDate, $endDate);

        return $this;
    }

    public function forPayer(?int $id = null) : self
    {
        $this->query->where('client_payer_id', $id);

        return $this;
    }

    public function isConfirmed(?boolean $confirmed = null): self
    {
        $this->confirmed = $confirmed;
    }

    public function isCharged($charged){
        $this->charged = $charged;
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