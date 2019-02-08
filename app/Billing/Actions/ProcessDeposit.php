<?php
namespace App\Billing\Actions;

use App\Billing\Deposit;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use App\Business;
use App\Caregiver;

class ProcessDeposit
{
    public function deposit(DepositMethodStrategy $strategy, array $depositAttributes, float $amount, string $currency = 'USD'): Deposit
    {
        if ($amount <= 0)  {
            throw new PaymentAmountError("The payment amount cannot be less than $0");
        }

        if (!$depositAttributes['deposit_type']) {
            throw new \InvalidArgumentException("A deposit_type attribute is required.");
        }

        if (!$transaction = $strategy->deposit($amount, $currency)) {
            throw new PaymentMethodError("Unable to get the transaction from the deposit method.");
        }

        $depositData = [
            'amount' => $amount,
            'transaction_id' => $transaction->id,
            'success' => $transaction->success,
        ];

        return Deposit::create($depositData + $depositAttributes);
    }

    public function depositToBusiness(Business $business, ?DepositMethodStrategy $strategy, float $amount, string $currency = 'USD'): Deposit
    {
        $account = $business->bankAccount;
        if (!$account) {
            throw new PaymentMethodError("There is no deposit account assigned to this business.");
        }

        if (!$strategy) {
            $strategy = $account->getDepositStrategy();
        }

        $attributes = [
            'deposit_type' => 'business',
            'business_id'  => $business->id,
        ];

        return $this->deposit($strategy, $attributes, $amount, $currency);
    }

    public function depositToCaregiver(Caregiver $caregiver, ?DepositMethodStrategy $strategy, float $amount, string $currency = 'USD'): Deposit
    {
        $account = $caregiver->bankAccount;
        if (!$account) {
            throw new PaymentMethodError("There is no deposit account assigned to this caregiver.");
        }

        if (!$strategy) {
            $strategy = $account->getDepositStrategy();
        }

        $attributes = [
            'deposit_type' => 'caregiver',
            'caregiver_id'  => $caregiver->id,
        ];

        return $this->deposit($strategy, $attributes, $amount, $currency);
    }
}