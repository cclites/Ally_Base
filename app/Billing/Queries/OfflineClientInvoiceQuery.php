<?php


namespace App\Billing\Queries;


use Illuminate\Database\Eloquent\Builder;

final class OfflineClientInvoiceQuery extends ClientInvoiceQuery
{
    public function __construct()
    {
        parent::__construct();
        $this->offlineOnly();
    }

    public function paidInFull(): self
    {
        $this->whereColumn('offline_amount_paid', '=', 'amount');

        return $this;
    }

    public function notPaidInFull(): self
    {
        $this->whereColumn('offline_amount_paid', '<', 'amount');

        return $this;
    }
    public function overpaid(): self
    {
        $this->whereColumn('offline_amount_paid', '>', 'amount');

        return $this;
    }
}