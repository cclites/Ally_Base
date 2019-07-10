<?php

namespace App\Billing\Gateway;

use App\Address;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Methods\BankAccount;
use App\PhoneNumber;

class OfflineAchFileGateway implements ACHPaymentInterface
{
    /**
     * @var \App\Billing\Gateway\AchExportFile
     */
    protected $ACHFile;

    /**
     * @var array
     */
    public $billing = [];

    public function __construct(AchExportFile $ACHFile)
    {
        $this->ACHFile = $ACHFile;
    }

    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     *
     * @return \App\Billing\GatewayTransaction|null
     */
    public function validateAccount(BankAccount $account)
    {
        return null;
    }

    /**
     * Deposit (credit) the account with $amount
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws PaymentMethodError
     * @throws \App\Billing\Exceptions\PaymentAmountError
     */
    public function depositFunds(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        $transaction = new GatewayTransaction([
            'gateway_id' => $this->ACHFile->getBankName(),
            'transaction_id' => $this->generateId(),
            'transaction_type' => 'credit',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => 0,
            'avs_pass' => 0,
        ]);

        $transaction->method()->associate($account);
        if (!$transaction->save()) {
            throw new PaymentMethodError("Could not record transaction.");
        }

        $id = $account->user_id;
        if ($account->business_id) {
            $id = 'B-' . $account->business_id;
        }

        $this->ACHFile->addTransaction($id, 'credit', $account, $amount);
        return $transaction;
    }

    /**
     * Generate unique transaction ID.
     *
     * @return string
     */
    private function generateId()
    {
        return $this->ACHFile->getBankName() . '-' . base_convert(bcmul(microtime(true), 1000), 10, 36);
    }

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     */
    public function authorizeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        return null;
    }

    /**
     * Charge the payment method
     *
     * @param \App\Billing\Payments\Methods\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return \App\Billing\GatewayTransaction
     * @throws \App\Billing\Exceptions\PaymentAmountError
     * @throws PaymentMethodError
     */
    public function chargeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        $transaction = new GatewayTransaction([
            'gateway_id' => $this->ACHFile->getBankName(),
            'transaction_id' => $this->generateId(),
            'transaction_type' => 'sale',
            'amount' => $amount,
            'success' => 1,
            'declined' => 0,
            'cvv_pass' => 0,
            'avs_pass' => 0,
        ]);

        $transaction->method()->associate($account);
        if (!$transaction->save()) {
            throw new PaymentMethodError("Could not record transaction.");
        }

        $id = $account->user_id;
        if ($account->business_id) {
            $id = 'B-' . $account->business_id;
        }

        $this->ACHFile->addTransaction($id, 'sale', $account, $amount);
        return $transaction;
    }

    /**
     * @param \App\Address $address
     * @return $this
     */
    public function setBillingAddress(Address $address)
    {
        $this->billing['address1'] = $address->address1;
        $this->billing['address2'] = $address->address2;
        $this->billing['city'] = $address->city;
        $this->billing['state'] = $address->state;
        $this->billing['zip'] = $address->zip;
        $this->billing['country'] = $address->country;
        return $this;
    }

    /**
     * @param \App\PhoneNumber $phone
     * @return $this
     */
    public function setBillingPhone(PhoneNumber $phone)
    {
        $this->billing['phone'] = $phone->national_number;
        return $this;
    }
}