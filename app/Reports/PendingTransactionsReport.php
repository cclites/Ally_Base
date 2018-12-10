<?php

namespace App\Reports;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Payments\BusinessDepositAggregator;
use App\Payments\BusinessPaymentAggregator;
use App\Payments\BusinessPaymentAggregatorWithoutClients;
use App\Payments\CaregiverDepositAggregator;
use App\Payments\ClientPaymentAggregator;
use Carbon\Carbon;

/**
 * Class PendingTransactionsReport
 * Note: Last transactions have been disabled as they caused an additional 15-20s in processing
 *
 * @package App\Reports
 */
class PendingTransactionsReport extends BaseReport
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

        return $collection->sortBy('name')->values();
    }

    protected function getBusinesses()
    {
        return Business::has('shifts')
                       ->get()
                       ->map(function (Business $business) {
                           $startDate = new Carbon('2017-01-01');
                           $endDate = Carbon::now($business->timezone)->startOfWeek();
                           // Use special aggregator to exclude provider pay from businesses (to avoid duplicates)
                           $paymentAggregator = new BusinessPaymentAggregatorWithoutClients($business, $startDate, $endDate);
                           $payment = $paymentAggregator->getPayment();
                           $depositAggregator = new BusinessDepositAggregator($business, $startDate, $endDate);
                           $deposit = $depositAggregator->getDeposit();

                           $flags = [];
                           if (!$business->paymentAccount) {
                               $flags[] = 'NO_PAYMENT_ACCOUNT';
                           }
                           if (!$business->bankAccount) {
                               $flags[] = 'NO_DEPOSIT_ACCOUNT';
                           }
                           if ($business->isOnHold()) {
                               $flags[] = 'ON_HOLD';
                           }

                           return [
                               'type'                => 'business',
                               'id'                  => $business->id,
                               'name'                => $business->name,
                               'business'            => $business->name,
                               'business_id'         => $business->id,
                               'payment_outstanding' => $payment->amount ?? 0.00,
                               'deposit_outstanding' => $deposit->amount ?? 0.00,
//                               'last_transaction_id' => $business->allTransactionsQuery()
//                                                                 ->orderBy('created_at', 'DESC')
//                                                                 ->value('id'),
                               'flags'               => implode(', ', $flags),
                           ];
                       });
    }

    protected function getCaregivers()
    {
        return Caregiver::has('shifts')
                        ->get()
                        ->map(function (Caregiver $caregiver) {
                            $businessChain = $caregiver->businessChains()->first();
                            $startDate = new Carbon('2017-01-01');
                            $endDate = Carbon::now('America/New_York')->startOfWeek();
                            $depositAggregator = new CaregiverDepositAggregator($caregiver, $startDate, $endDate);
                            $deposit = $depositAggregator->getDeposit();

                            $flags = [];
                            if (!$caregiver->bankAccount) {
                                $flags[] = 'NO_BANK_ACCOUNT';
                            }
                            if ($caregiver->isOnHold()) {
                                $flags[] = 'ON_HOLD';
                            }

                            return [
                                'type'                => 'caregiver',
                                'id'                  => $caregiver->id,
                                'name'                => $caregiver->nameLastFirst(),
                                'business'            => $businessChain->name,
                                'business_chain_id'   => $businessChain->id,
                                'payment_outstanding' => 0.00,
                                'deposit_outstanding' => $deposit->amount ?? 0.00,
//                                'last_transaction_id' => $caregiver->allTransactionsQuery()
//                                                                   ->orderBy('created_at', 'DESC')
//                                                                   ->value('id'),
                                'flags'               => implode(', ', $flags),
                            ];
                        });
    }

    protected function getClients()
    {
        return Client::has('shifts')
                     ->get()
                     ->map(function (Client $client) {
                         $business = $client->business;
                         $startDate = new Carbon('2017-01-01');
                         $endDate = Carbon::now($business->timezone)->startOfWeek();
                         $paymentAggregator = new ClientPaymentAggregator($client, $startDate, $endDate);
                         $payment = $paymentAggregator->getPayment();

                         $flags = [];
                         if (!$client->defaultPayment) {
                             $flags[] = 'NO_PAYMENT_METHOD';
                         }
                         if ($client->isOnHold()) {
                             $flags[] = 'ON_HOLD';
                         }

                         return [
                             'type'                => 'client',
                             'id'                  => $client->id,
                             'name'                => $client->nameLastFirst(),
                             'business'            => $business->name,
                             'business_id'         => $business->id,
                             'payment_outstanding' => $payment->amount ?? 0.00,
                             'deposit_outstanding' => 0.00,
//                             'last_transaction_id' => $client->allTransactionsQuery()
//                                                             ->orderBy('created_at', 'DESC')
//                                                             ->value('id'),
                             'flags'               => implode(', ', $flags),
                         ];
                     });
    }
}
