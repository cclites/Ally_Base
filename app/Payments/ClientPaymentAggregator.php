<?php
namespace App\Payments;

use App\BankAccount;
use App\Client;
use App\Contracts\ChargeableInterface;
use App\CreditCard;
use App\Gateway\ECSPayment;
use App\Payment;
use App\Shifts\AllyFeeCalculator;
use App\Shift;
use Carbon\Carbon;
use DB;

class ClientPaymentAggregator
{

    /**
     * @var \App\Client
     */
    protected $client;

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

    public function __construct(Client $client, Carbon $startDate, Carbon $endDate)
    {
        $this->client = $client;
        $this->method = $client->getPaymentMethod();
        $this->startDate = $startDate->copy()->setTimezone('UTC');
        $this->endDate = $endDate->copy()->setTimezone('UTC');

        $this->shifts = Shift::isAwaitingCharge()
            ->whereNull('payment_id')
            ->whereBetween('checked_in_time', [$this->startDate, $this->endDate])
            ->where('client_id', $this->client->id)
            ->get();

    }

    public function getPayment()
    {
        $payment = new Payment([
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'payment_type' => $this->client->getPaymentType(),
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

        $payment->load('client');

        return $payment;
    }

    public function getAllPendingShifts()
    {
        return Shift::whereIn('status', [Shift::WAITING_FOR_CHARGE, Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_APPROVAL])
            ->whereNull('payment_id')
            ->whereBetween('checked_in_time', [$this->startDate, $this->endDate])
            ->where('client_id', $this->client->id)
            ->get();
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

        try {
            // Process Payment and Update Status in a Transaction
            DB::beginTransaction();

            // Persist the payment
            $payment->save();

            // Attempt to update status of all shifts
            foreach($this->getShifts() as $shift) {
                if (!$shift->status()->ackPayment($payment->id)) {
                    DB::rollBack();
                    return false;
                }
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