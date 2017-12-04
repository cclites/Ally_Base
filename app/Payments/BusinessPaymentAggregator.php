<?php
namespace App\Payments;

use App\Business;
use App\Contracts\ChargeableInterface;
use App\Payment;
use App\Shift;
use Carbon\Carbon;

class BusinessPaymentAggregator
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

    public function __construct(Business $business, Carbon $startDate, Carbon $endDate)
    {
        $this->business = $business;
        $this->method = $business->paymentAccount;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->shifts = Shift::whereIn('status', [Shift::WAITING_FOR_CHARGE])
            ->whereNull('payment_id')
            ->whereBetween('checked_in_time', [$this->startDate, $this->endDate])
            ->whereIn('client_id', $this->getClientsUsingProviderPayment())
            ->get();
    }

    public function getClientsUsingProviderPayment()
    {
        return $this->business->clientsUsingProviderPayment;
    }

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

        foreach($this->shifts as $shift) {
            $payment->amount += $shift->costs()->getTotalCost();
            $payment->business_allotment += $shift->costs()->getProviderFee();
            $payment->caregiver_allotment += $shift->costs()->getCaregiverCost();
            $payment->system_allotment += $shift->costs()->getAllyFee();
        }

        $payment->load('business');

        return $payment;
    }

    public function getShifts()
    {
        return $this->shifts;
    }

    public function getShiftIds()
    {
        return $this->getShifts()->pluck('id')->toArray();
    }

    /**
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
        $transaction = $this->method->charge($payment->amount);

        if ($transaction) {
            $payment->fill([
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
            // Save the payment to the client
            $this->business->payments()->save($payment);
            // Update shifts' payment id
            Shift::whereIn('id', $this->getShiftIds())->update([
                'payment_id' => $payment->id,
                'status' => Shift::WAITING_FOR_PAYOUT,
            ]);
        }

        return $transaction;
    }
}