<?php
namespace App\Payments;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Caregiver;
use App\Billing\Deposit;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ECSPayment;

/**
 * Class SingleDepositProcessor
 *
 * Use for manual deposits only!  (use DepositProcessor for automated transactions utilizing shift data)
 *
 * @package App\Payments
 */
class SingleDepositProcessor
{
    /**
     * @return ACHDepositInterface
     */
    protected static function gateway()
    {
        return new ECSPayment();
    }

    public static function depositCaregiver(Caregiver $caregiver, $amount, $adjustment = false, $notes = null)
    {
        $account = $caregiver->bankAccount;
        if ($transaction = self::handleTransaction($account, $amount)) {
            $deposit = Deposit::create([
                'deposit_type' => 'caregiver',
                'caregiver_id' => $caregiver->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'adjustment' => $adjustment,
                'notes' => $notes,
                'success' => $transaction->success,
            ]);
        }

        return $transaction;
    }

    public static function depositBusiness(Business $business, $amount, $adjustment = false, $notes = null)
    {
        $account = $business->bankAccount;
        if ($transaction = self::handleTransaction($account, $amount)) {
            $deposit = Deposit::create([
                'deposit_type' => 'business',
                'business_id' => $business->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'adjustment' => $adjustment,
                'notes' => $notes,
                'success' => $transaction->success,
            ]);
        }

        return $transaction;
    }

    protected static function handleTransaction(BankAccount $account, $amount)
    {
        if ($amount > 0) {
            return self::gateway()->depositFunds($account, $amount);
        }
        if ($amount < 0) {
            $amount = $amount * -1.0;
            return $account->charge($amount);
        }
        return false;
    }

}