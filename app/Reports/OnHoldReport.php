<?php

namespace App\Reports;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Payments\BusinessDepositAggregator;
use App\Payments\BusinessPaymentAggregator;
use App\Payments\CaregiverDepositAggregator;
use App\Payments\ClientPaymentAggregator;
use Carbon\Carbon;

class OnHoldReport extends BaseReport
{

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return false;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $collection = collect(array_merge(
            $this->getBusinesses()->toArray(),
            $this->getCaregivers()->toArray(),
            $this->getClients()->toArray()
        ));

        return $collection->sortBy('name');
    }

    protected function getBusinesses()
    {
        return Business::has('paymentHold')->with('paymentHold')
                       ->get()
                       ->map(function (Business $business) {
                           $startDate = new Carbon('2017-01-01');
                           $endDate = Carbon::now($business->timezone)->startOfWeek();
                           $paymentAggregator = new BusinessPaymentAggregator($business, $startDate, $endDate);
                           $payment = $paymentAggregator->getPayment();
                           $depositAggregator = new BusinessDepositAggregator($business, $startDate, $endDate);
                           $deposit = $depositAggregator->getDeposit();

                           return [
                               'type'                => 'business',
                               'id'                  => $business->id,
                               'name'                => $business->name,
                               'business'            => $business->name,
                               'payment_outstanding' => $payment->amount ?? 0.00,
                               'deposit_outstanding' => $deposit->amount ?? 0.00,
                               'last_transaction_id' => $business->allTransactionsQuery()
                                                                 ->orderBy('created_at', 'DESC')
                                                                 ->value('id'),
                               'created_at'          => $business->paymentHold->created_at->toDateTimeString(),
                           ];
                       });
    }

    protected function getCaregivers()
    {
        return Caregiver::has('paymentHold')->with('paymentHold')
                        ->get()
                        ->map(function (Caregiver $caregiver) {
                            $business = $caregiver->businesses()->first();
                            $startDate = new Carbon('2017-01-01');
                            $endDate = Carbon::now($business->timezone)->startOfWeek();
                            $depositAggregator = new CaregiverDepositAggregator($caregiver, $startDate, $endDate);
                            $deposit = $depositAggregator->getDeposit();

                            return [
                                'type'                => 'caregiver',
                                'id'                  => $caregiver->id,
                                'name'                => $caregiver->nameLastFirst(),
                                'business'            => $business->name,
                                'payment_outstanding' => 0.00,
                                'deposit_outstanding' => $deposit->amount ?? 0.00,
                                'last_transaction_id' => $caregiver->allTransactionsQuery()
                                                                   ->orderBy('created_at', 'DESC')
                                                                   ->value('id'),
                                'created_at'          => $caregiver->paymentHold->created_at->toDateTimeString(),
                            ];
                        });
    }

    protected function getClients()
    {
        return Client::has('paymentHold')->with('paymentHold')
                     ->get()
                     ->map(function (Client $client) {
                         $business = $client->business;
                         $startDate = new Carbon('2017-01-01');
                         $endDate = Carbon::now($business->timezone)->startOfWeek();
                         $paymentAggregator = new ClientPaymentAggregator($client, $startDate, $endDate);
                         $payment = $paymentAggregator->getPayment();

                         return [
                             'type'                => 'client',
                             'id'                  => $client->id,
                             'name'                => $client->nameLastFirst(),
                             'business'            => $business->name,
                             'payment_outstanding' => $payment->amount ?? 0.00,
                             'deposit_outstanding' => 0.00,
                             'last_transaction_id' => $client->allTransactionsQuery()
                                                             ->orderBy('created_at', 'DESC')
                                                             ->value('id'),
                             'created_at'          => $client->paymentHold->created_at->toDateTimeString(),
                         ];
                     });
    }
}