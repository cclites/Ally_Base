<?php

namespace App\Reports;

use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Billing\Queries\OnlineClientInvoiceQuery;
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
                               'id'                  => $business->paymentHold->id,
                               'user_id'             => $business->paymentHold->user_id,
                               'business_id'         => $business->paymentHold->business_id,
                               'name'                => $business->name,
                               'business'            => $business->name,
                               'payment_outstanding' => $payment->amount ?? 0.00,
                               'deposit_outstanding' => $deposit->amount ?? 0.00,
                               'last_transaction_id' => $business->allTransactionsQuery()
                                                                 ->orderBy('created_at', 'DESC')
                                                                 ->value('id'),
                               'created_at'          => $business->paymentHold->created_at->toDateTimeString(),
                               'unpaid_invoices'     => app(BusinessInvoiceQuery::class)->forBusiness($business->id)->notPaidInFull()->count(),
                               'notes'               => $business->paymentHold->notes,
                               'check_back_on'       => $business->paymentHold->check_back_on,
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
                                'id'                  => $caregiver->paymentHold->id,
                                'user_id'             => $caregiver->paymentHold->user_id,
                                'business_id'         => $caregiver->paymentHold->business_id,
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
                                'notes'               => $caregiver->paymentHold->notes,
                                'check_back_on'       => $caregiver->paymentHold->check_back_on,
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
                             'id'                  => $client->paymentHold->id,
                             'user_id'             => $client->paymentHold->user_id,
                             'business_id'         => $client->paymentHold->business_id,
                             'name'                => $client->nameLastFirst(),
                             'business'            => $business->name,
                             'payment_outstanding' => $payment->amount ?? 0.00,
                             'deposit_outstanding' => 0.00,
                             'last_transaction_id' => $client->allTransactionsQuery()
                                                             ->orderBy('created_at', 'DESC')
                                                             ->value('id'),
                             'created_at'          => $client->paymentHold->created_at->toDateTimeString(),
                             'unpaid_invoices'     => app(OnlineClientInvoiceQuery::class)->forClient($client->id)->notPaidInFull()->count(),
                             'notes'               => $client->paymentHold->notes,
                             'check_back_on'       => $client->paymentHold->check_back_on,
                         ];
                     });
    }
}