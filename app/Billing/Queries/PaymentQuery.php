<?php
namespace App\Billing\Queries;

use App\Billing\Exceptions\PayerAssignmentError;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Business;
use App\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentQuery extends BaseQuery
{
    use BelongsToBusinessesQueries;

    /**
     * Return an empty instance of the Model this class queries
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function getModelInstance(): Model
    {
        return new Payment();
    }

    function forClient(Client $client): self
    {
        $this->where('client_id', $client->id);

        return $this;
    }

    function usingProviderPay(?Business $business): self
    {
        $this->where('payment_method_type', maps_from_class(Business::class));
        if ($business) $this->where('payment_method_id', $business->id);

        return $this;
    }

    function hasAmountAvailable(): self
    {
        $this->where('success', true)
            ->where('created_at', '>=', '2019-01-01 00:00:00') // Prevent pre-migration missing applications from showing as available payments
            ->whereRaw('(SELECT COALESCE(SUM(amount_applied), 0) FROM invoice_payments WHERE payment_id = payments.id) < payments.amount');

        return $this;
    }

    /**
     * Get all payments for a calendar year.
     *
     * @param int $year
     * @return $this
     */
    public function forYear(int $year) : self
    {
        $range = [
            Carbon::parse("$year/01/01 00:00:01"),
            Carbon::parse("$year/12/31 23:59:59")
        ];

        $this->whereBetween('created_at', $range);

        return $this;
    }
}