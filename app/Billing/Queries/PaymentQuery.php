<?php
namespace App\Billing\Queries;

use App\Billing\Exceptions\PayerAssignmentError;
use App\Billing\Payer;
use App\Billing\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentQuery extends BaseQuery
{

    /**
     * Return an empty instance of the Model this class queries
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function getModelInstance(): Model
    {
        return new Payment();
    }

    function forPayer(Payer $payer): self
    {
        if ($payer->isPrivatePay()) {
            if (!$client = $payer->getPrivatePayer()) {
                throw new PayerAssignmentError("Using PaymentQuery::forPayer with an unattached private payer.");
            }
            $this->where('client_id', $client->id);
        }

        $this->where('payer_id', $payer->id);

        return $this;
    }

    function hasAmountAvailable(): self
    {
        $this->where('success', true)
            ->whereRaw('(SELECT COALESCE(SUM(amount_applied), 0) FROM invoice_payments WHERE payment_id = payments.id) < payments.amount');

        return $this;
    }
}