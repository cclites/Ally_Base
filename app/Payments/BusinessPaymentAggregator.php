<?php
namespace App\Payments;

use App\Business;
use App\Contracts\ChargeableInterface;
use App\Contracts\PaymentAggregatorInterface;
use App\Payment;
use App\Shift;
use Carbon\Carbon;
use DB;

class BusinessPaymentAggregator implements PaymentAggregatorInterface
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * @var \App\Contracts\ChargeableInterface
     */
    protected $method;

    /**
     * @var Shift[]
     */
    protected $shifts;

    /**
     * @var \Carbon\Carbon
     */
    private $startDate;

    /**
     * @var \Carbon\Carbon
     */
    private $endDate;

    /**
     * Store for client ids using provider payment
     * @var array
     */
    private $clientIds = [];

    public function __construct(Business $business, Carbon $startDate, Carbon $endDate)
    {
        $this->business = $business;
        $this->method = $business->paymentAccount;
        $this->startDate = $startDate->copy()->setTimezone('UTC');
        $this->endDate = $endDate->copy()->setTimezone('UTC');
    }

    /**
     * Get all client models using the provider payment method
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Client[]
     */
    public function getClientsUsingProviderPayment()
    {
        return $this->business->clientsUsingProviderPayment()
                              ->doesntHave('paymentHold')
                              ->get();
    }

    /**
     * Get all client IDs using the provider payment method
     *
     * @return array
     */
    public function getClientIdsUsingProviderPayment()
    {
        if (!$this->clientIds) {
            $this->clientIds = $this->getClientsUsingProviderPayment()
                                    ->pluck('id')
                                    ->toArray();
        }
        return $this->clientIds;
    }

    /**
     * Get all client models NOT using the provider payment method
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Client[]
     */
    public function getClientsNotUsingProviderPayment()
    {
        return $this->business->clients()
                              ->doesntHave('paymentHold')
                              ->whereNotIn('id', $this->getClientIdsUsingProviderPayment())
                              ->get();
    }

    /**
     * Get an unsaved version of the aggregated payment model
     *
     * @return \App\Payment
     */
    public function getPayment()
    {
        $payment = new Payment([
            'client_id' => null,
            'business_id' => $this->business->id,
            'payment_type' => 'ACH',
            'amount' => 0,
            'business_allotment' => 0,
            'caregiver_allotment' => 0,
            'system_allotment' => 0,
        ]);

        foreach($this->getShifts() as $shift) {
            $payment->amount += $shift->costs()->getTotalCost();
            $payment->business_allotment += $shift->costs()->getProviderFee();
            $payment->caregiver_allotment += $shift->costs()->getCaregiverCost();
            $payment->system_allotment += $shift->costs()->getAllyFee();
        }

        $payment->load('business');

        return $payment;
    }

    /**
     * Return the shift models relating to this payment
     *
     * @return \App\Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getShifts()
    {
        if (!$this->shifts) {
            $this->shifts = Shift::whereAwaitingCharge()
                                 ->whereNull('payment_id')
                                 ->whereBetween('checked_in_time', [$this->startDate, $this->endDate])
                                 ->whereIn('client_id', $this->getClientIdsUsingProviderPayment())
                                 ->get();
        }

        return $this->shifts;
    }

    /**
     * Return the shift IDs relating to this payment
     *
     * @return array
     */
    public function getShiftIds()
    {
        return $this->getShifts()->pluck('id')->toArray();
    }

    /**
     * Charge and persist the payment
     *
     * @return \App\GatewayTransaction|false
     */
    public function charge()
    {
        $payment = $this->getPayment();

        if ($payment->amount == 0) {
            return false;
        }

        if (!$this->method instanceof ChargeableInterface) {
            return false;
        }

        try {
            // Process Payment and Update Status in a Transaction
            DB::beginTransaction();

            // Persist the payment
            $payment->save();

            // Attempt to update status of all shifts
            foreach($this->getShifts() as $shift) {
                if (!$shift->statusManager()->ackPayment($payment->id)) {
                    DB::rollBack();
                    return false;
                }
                // Persist shift costs
                $shift->costs()->setPaymentType($this->method)->persist();
            }

            // Process Payments
            if (!$transaction = $this->method->charge($payment->amount)) {
                DB::rollBack();
                return false;
            }

            // Commit database transaction now that payment has completed
            DB::commit();

            // Save transaction details to payment
            $payment->update([
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);

            return $transaction;
        }
        catch(\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}