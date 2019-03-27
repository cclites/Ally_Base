<?php

namespace App\Reports;

use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Billing\Queries\ClientInvoiceQuery;
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
    protected $businessId;

    /**
     * Set business ID filter.
     *
     * @param $businessId
     */
    public function forBusiness(?int $businessId) : void
    {
        if (empty($businessId)) {
            $this->businessId = null;
        }
        $this->businessId = $businessId;
    }

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
        $query = Business::has('paymentHold')->with('paymentHold');
        if ($this->businessId) {
            $query->where('id', $this->businessId);
        }
        return $query->get()
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
                               'business_id'         => $business->id,
                               'payment_outstanding' => $payment->amount ?? 0.00,
                               'deposit_outstanding' => $deposit->amount ?? 0.00,
                               'last_transaction_id' => $business->allTransactionsQuery()
                                                                 ->orderBy('created_at', 'DESC')
                                                                 ->value('id'),
                               'created_at'          => $business->paymentHold->created_at->toDateTimeString(),
                               'unpaid_invoices'     => app(BusinessInvoiceQuery::class)->forBusiness($business->id)->notPaidInFull()->count(),
                           ];
                       });
    }

    protected function getCaregivers()
    {
        $query = Caregiver::has('paymentHold')->with('paymentHold');
        if ($this->businessId) {
            $query->forBusinesses([$this->businessId]);
        }
        return $query->get()
                        ->map(function (Caregiver $caregiver) {
                            $businessChain = $caregiver->businessChains()->first();
                            $startDate = new Carbon('2017-01-01');
                            $endDate = Carbon::now('America/New_York')->startOfWeek();
                            $depositAggregator = new CaregiverDepositAggregator($caregiver, $startDate, $endDate);
                            $deposit = $depositAggregator->getDeposit();

                            return [
                                'type'                => 'caregiver',
                                'id'                  => $caregiver->id,
                                'name'                => $caregiver->nameLastFirst(),
                                'business'            => $businessChain->name,
                                'business_chain_id'   => $businessChain->id,
                                'payment_outstanding' => 0.00,
                                'deposit_outstanding' => $deposit->amount ?? 0.00,
                                'last_transaction_id' => $caregiver->allTransactionsQuery()
                                                                   ->orderBy('created_at', 'DESC')
                                                                   ->value('id'),
                                'created_at'          => $caregiver->paymentHold->created_at->toDateTimeString(),
                                'unpaid_invoices'     => app(CaregiverInvoiceQuery::class)->forCaregiver($caregiver->id)->notPaidInFull()->count(),
                            ];
                        });
    }

    protected function getClients()
    {
        $query = Client::has('paymentHold')->with('paymentHold');
        if ($this->businessId) {
            $query->where('business_id', $this->businessId);
        }
        return $query->get()
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
                             'business_id'         => $business->id,
                             'payment_outstanding' => $payment->amount ?? 0.00,
                             'deposit_outstanding' => 0.00,
                             'last_transaction_id' => $client->allTransactionsQuery()
                                                             ->orderBy('created_at', 'DESC')
                                                             ->value('id'),
                             'created_at'          => $client->paymentHold->created_at->toDateTimeString(),
                             'unpaid_invoices'     => app(ClientInvoiceQuery::class)->forClient($client->id)->notPaidInFull()->count(),
                         ];
                     });
    }
}