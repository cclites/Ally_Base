<?php
namespace App\Payments;

use App\BankAccount;
use App\Client;
use App\CreditCard;
use App\Gateway\ECSPayment;
use App\Payment;
use App\Scheduling\AllyFeeCalculator;
use App\Shift;

class ClientPaymentAggregator
{

    protected $client;
    protected $method;
    protected $shifts;

    public function __construct(Client $client, $startDate, $endDate)
    {
        $this->client = $client;
        $this->method = $client->defaultPayment;

        $report = new \App\Reports\ScheduledPaymentsReport();
        $report->between($startDate, $endDate);
        $this->shifts = $report->rows()->where('client_id', $client->id);
    }

    public function getData()
    {
        $authorizedShifts = $this->shifts->where('status', Shift::WAITING_FOR_CHARGE);

        $data = [
            'mileage' => 0,
            'total_payment' => 0,
            'caregiver_allotment' => 0,
            'business_allotment' => 0,
            'ally_allotment' => 0,
        ];
        $shiftIds = [];

        foreach($authorizedShifts as $shift) {
            foreach($data as $index=>$value) {
                $data[$index] = round(bcadd($data[$index], $shift[$index], 4), 2);
            }
            $shiftIds[] = $shift['shift_id'];
        }

        $data = $this->addMileageExpense($data);

        $data['client_id'] = $this->client->id;
        $data['client_name'] = $this->client->nameLastFirst();
        $data['payment_type'] = $this->client->getPaymentType();
        $data['total_shifts'] = $this->shifts->count();
        $data['unauthorized_shifts'] = $this->shifts->count() - $authorizedShifts->count();
        $data['shifts'] = $shiftIds;

        return $data;
    }

    public function addMileageExpense($data) {
        $business = $this->client->business;
        $calc = new MileageExpenseCalculator($this->client, $business, $this->method, $data['mileage']);

        $data['total_payment'] = bcadd($data['total_payment'], $calc->getTotalCost(), 2);
        $data['ally_allotment'] = bcadd($data['ally_allotment'], $calc->getAllyFee(), 2);
        $data['caregiver_allotment'] = bcadd($data['caregiver_allotment'], $calc->getCaregiverReimbursement(), 2);

        return $data;
    }

    /**
     * @return \App\GatewayTransaction|false
     */
    public function charge()
    {
        $data = $this->getData();
        $gateway = new ECSPayment();

        if ($this->method instanceof CreditCard) {
            $transaction = $gateway->chargeCard($this->method, $data['total_payment']);
        }
        elseif ($this->method instanceof BankAccount) {
            $transaction = $gateway->chargeAccount($this->method, $data['total_payment']);
        }
        else {
            return false;
        }

        if ($transaction) {
            $payment = new Payment([
                'business_id' => $this->client->business_id,
                'payment_type' => $data['payment_type'],
                'amount' => $data['total_payment'],
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
                'caregiver_allotment' => $data['caregiver_allotment'],
                'business_allotment' => $data['business_allotment'],
                'system_allotment' => $data['ally_allotment'],
            ]);
            // Save the payment to the client
            $this->client->payments()->save($payment);
            // Update shifts' payment id
            Shift::whereIn('id', $data['shifts'])->update([
                'payment_id' => $payment->id,
                'status' => Shift::WAITING_FOR_PAYOUT,
            ]);
        }

        return $transaction;
    }
}