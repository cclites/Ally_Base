<?php
namespace App\Billing\Queries;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Payer;
use App\BusinessChain;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientInvoiceQuery
 * @package App\Billing\Queries
 *
 *
 */
final class OnlineClientInvoiceQuery extends ClientInvoiceQuery
{
    public function __construct()
    {
        parent::__construct();
        $this->onlineOnly();
    }

    public function paidInFull(): self
    {
        $this->whereColumn('amount_paid', '=', 'amount');

        return $this;
    }

    function notPaidInFull(): self
    {
        $this->whereColumn('amount_paid', '!=', 'amount');

        return $this;
    }

    function notOnHold(): self
    {
        $this->whereDoesntHave('client', function($q) {
            $q->has('paymentHold');
        });

        return $this;
    }

}