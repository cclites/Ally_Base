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
        $this->whereHas('claim', function(Builder $builder) {
            $builder->whereColumn('amount_paid', '=', 'amount');
        });

        return $this;
    }

    public function notPaidInFull(): self
    {
        $this->where(function(Builder $builder) {
            $builder->whereHas('claim', function (Builder $builder) {
                $builder->whereColumn('amount_paid', '=', 'amount');
            })->orDoesntHave('claim');
        });

        return $this;
    }
}