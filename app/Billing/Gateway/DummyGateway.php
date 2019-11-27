<?php

namespace App\Billing\Gateway;

use App\Address;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\PhoneNumber;

class DummyGateway implements ACHPaymentInterface, CreditCardPaymentInterface
{
    public function validateAccount(BankAccount $account)
    {
        return null;
    }

    public function depositFunds(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        return new GatewayTransaction([
            'gateway_id' => 'dummy',
            'transaction_id' => rand(1000,999999),
            'transaction_type' => 'credit',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => true,
            'avs_pass' => true,
            'response_text' => null,
            'response_data' => 'success',
            'account_number' => $account->last_four,
            'routing_number' => $account->routing_number,
        ]);
    }

    public function authorizeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        return null;
    }

    public function chargeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        return new GatewayTransaction([
            'gateway_id' => 'dummy',
            'transaction_id' => rand(1000,999999),
            'transaction_type' => 'credit',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => true,
            'avs_pass' => true,
            'response_text' => null,
            'response_data' => 'success',
            'account_number' => $account->last_four,
            'routing_number' => $account->routing_number,
        ]);
    }

    public function setBillingAddress(Address $address)
    {
        return $this;
    }

    public function setBillingPhone(PhoneNumber $phone)
    {
        return $this;
    }

    public function validateCard(CreditCard $card, $cvv = null)
    {
        return null;
    }

    public function authorizeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null)
    {
        return null;
    }

    public function chargeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null)
    {
        return new GatewayTransaction([
            'gateway_id' => 'dummy',
            'transaction_id' => rand(1000,999999),
            'transaction_type' => 'sale',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => true,
            'avs_pass' => true,
            'response_text' => null,
            'response_data' => 'success',
            'account_number' => null,
            'routing_number' => null,
        ]);
    }

    public function refund(GatewayTransaction $transaction, $amount)
    {
        return new GatewayTransaction([
            'gateway_id' => 'dummy',
            'transaction_id' => rand(1000,999999),
            'transaction_type' => 'refund',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => true,
            'avs_pass' => true,
            'response_text' => null,
            'response_data' => 'success',
            'account_number' => null,
            'routing_number' => null,
        ]);
    }
}