<?php

namespace App\Billing\Gateway;

use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Methods\BankAccount;

class HeritiageACHService implements ACHDepositInterface
{
    /**
     * @var \App\Billing\Gateway\HeritageACHFile
     */
    protected $ACHFile;

    public function __construct(HeritageACHFile $ACHFile)
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
            'gateway_id' => 'heritage',
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
        return 'heritage-' . base_convert(bcmul(microtime(true), 1000), 10, 36);
    }
}