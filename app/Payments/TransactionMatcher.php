<?php

namespace App\Payments;

use App\BankAccount;
use App\Business;
use App\CreditCard;
use App\Gateway\ECSQuery;
use App\User;

class TransactionMatcher {
    /**
     * @var \App\Gateway\ECSQuery
     */
    private $query;

    public function __construct(ECSQuery $query = null)
    {
        $this->query = $query ? $query : new ECSQuery();
    }

    /**
     * Find a payment method matching the transaction information
     *
     * @param $transaction_id
     * @return \App\Contracts\ChargeableInterface|null
     */
    public function findMethod($transaction_id)
    {
        $result = $this->query->find($transaction_id);
        if ($result && $result->transaction) {
            $result = $result->transaction;
            switch ($result->transaction_type) {
                case 'cc':
                    return $this->matchingCards( (string) $result->cc_number, (string) $result->cc_exp )->first();
                    break;
                case 'ck':
                    return $this->matchingAccounts( (string) $result->check_account, (string) $result->check_aba )->first();
                    break;
            }
        }
        return null;
    }

    /**
     * @param string $reportedNumber
     * @param string $reportedExpiration
     * @return \Illuminate\Support\Collection
     */
    protected function matchingCards($reportedNumber, $reportedExpiration)
    {
        $cards = CreditCard::select(['id', 'number'])
                         ->get()
                         ->filter(function ($item) use ($reportedNumber) {
                             // Match the first number and last 4 numbers of the card number
                             return (
                                 substr($reportedNumber, 0, 1) === substr($item->number, 0, 1)
                                 && substr($reportedNumber, -4) === substr($item->number, -4)
                             );
                         })->map(function ($item) {
                             return CreditCard::find($item->id);
                         });
        if ($cards->count() < 2) {
            return $cards;
        }
        // Filter by expiration date if more than 1
        $filtered = $cards->filter(function($item) use ($reportedExpiration) {
            $expirationDate = $item->expiration_month . substr($item->expiration_year, -2);
            return ($expirationDate == $reportedExpiration);
        });
        return ($filtered->count()) ? $filtered : $cards;
    }

    /**
     * @param string $reportedAccountNumber
     * @param string $reportedRoutingNumber
     * @return \Illuminate\Support\Collection
     */
    protected function matchingAccounts($reportedAccountNumber, $reportedRoutingNumber)
    {
        return BankAccount::select(['id', 'account_number', 'routing_number'])
                          ->get()
                        ->filter(function ($item) use ($reportedAccountNumber, $reportedRoutingNumber) {
                            // Match the last 4 numbers of the account number and the entire routing number
                            return (
                                substr($reportedAccountNumber, -4) === substr($item->account_number, -4)
                                && $reportedRoutingNumber == $item->routing_number
                            );
                        })->map(function ($item) {
                            return BankAccount::find($item->id);
                        });
    }

    /**
     * Find a user matching the transaction information
     *
     * @param $transaction_id
     * @return \App\User|null
     */
    protected function findUser($transaction_id)
    {
        if ($method = $this->findMethod($transaction_id)) {
            if ($method->user_id) return User::find($method->user_id);
        }
        return null;
    }

    /**
     * Find a business matching the transaction bank information (Deposited to or withdrawn from a business account)
     *
     * @param $transaction_id
     * @return \App\Business|null
     */
    protected function findBusiness($transaction_id)
    {
        if ($method = $this->findMethod($transaction_id)) {
            if ($method->business_id) return Business::find($method->business_id);
        }
        return null;
    }
}