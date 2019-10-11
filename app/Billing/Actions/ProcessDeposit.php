<?php
namespace App\Billing\Actions;

use App\Billing\Deposit;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use App\Billing\Payments\DepositMethodFactory;
use App\Business;
use App\Caregiver;

class ProcessDeposit
{
    /**
     * Process strategy deposit.
     *
     * @param DepositMethodStrategy $strategy
     * @param array $depositAttributes
     * @param float $amount
     * @param string $currency
     * @param bool $allowZero
     * @return Deposit
     * @throws PaymentAmountError
     * @throws PaymentMethodError
     */
    public function deposit(DepositMethodStrategy $strategy, array $depositAttributes, float $amount, string $currency = 'USD', bool $allowZero = false): Deposit
    {
        if ($amount < 0)  {
            throw new PaymentAmountError("The payment amount cannot be less than $0");
        }

        if (!$depositAttributes['deposit_type']) {
            throw new \InvalidArgumentException("A deposit_type attribute is required.");
        }

        if (floatval($amount) === floatval(0)) {
            if (! $allowZero) {
                throw new PaymentAmountError("The payment amount cannot be $0");
            }
            // If a zero amount was passed just fake the transaction.
            $depositData = [
                'amount' => $amount,
                'success' => 1,
            ];
        }
        else {
            if (!$transaction = $strategy->deposit($amount, $currency)) {
                throw new PaymentMethodError("Unable to get the transaction from the deposit method.");
            }

            $depositData = [
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ];
        }

        return Deposit::create($depositData + $depositAttributes);
    }

    /**
     * Process business deposit.
     *
     * @param Business $business
     * @param DepositMethodFactory $methodFactory
     * @param float $amount
     * @param string $currency
     * @param bool $allowZero
     * @return Deposit
     * @throws PaymentAmountError
     * @throws PaymentMethodError
     */
    public function depositToBusiness(Business $business, DepositMethodFactory $methodFactory, float $amount, string $currency = 'USD', bool $allowZero = false): Deposit
    {
        $account = $business->bankAccount;
        if (!$account) {
            throw new PaymentMethodError("There is no deposit account assigned to this business.");
        }

        $strategy = $methodFactory->getStrategy($account);

        $attributes = [
            'deposit_type' => 'business',
            'business_id'  => $business->id,
            'chain_id' => $business->chain_id
        ];

        return $this->deposit($strategy, $attributes, $amount, $currency, $allowZero);
    }

    /**
     * Process caregiver deposit.
     *
     * @param Caregiver $caregiver
     * @param DepositMethodFactory $methodFactory
     * @param float $amount
     * @param string $currency
     * @param bool $allowZero
     * @return Deposit
     * @throws PaymentAmountError
     * @throws PaymentMethodError
     */
    public function depositToCaregiver(Caregiver $caregiver, DepositMethodFactory $methodFactory, float $amount, string $currency = 'USD', bool $allowZero = false, int $chainId): Deposit
    {
        $account = $caregiver->bankAccount;
        if (!$account) {
            throw new PaymentMethodError("There is no deposit account assigned to this caregiver.");
        }

        $strategy = $methodFactory->getStrategy($account);

        $attributes = [
            'deposit_type' => 'caregiver',
            'caregiver_id'  => $caregiver->id,
            'chain_id' => $chainId
        ];

        return $this->deposit($strategy, $attributes, $amount, $currency, $allowZero);
    }
}