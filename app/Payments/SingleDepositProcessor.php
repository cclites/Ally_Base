<?php
namespace App\Payments;
use App\Business;
use App\Caregiver;
use App\Deposit;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ECSPayment;

class SingleDepositProcessor
{
    /**
     * @return ACHDepositInterface
     */
    protected static function gateway()
    {
        return new ECSPayment();
    }

    public static function depositCaregiver(Caregiver $caregiver, $amount)
    {
        $account = $caregiver->bankAccount;
        if ($transaction = self::gateway()->depositFunds($account, $amount)) {
            $deposit = Deposit::create([
                'deposit_type' => 'caregiver',
                'caregiver_id' => $caregiver->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
            $deposit->method()->associate($account);
        }
        return $transaction;
    }

    public static function depositBusiness(Business $business, $amount)
    {
        $account = $business->bankAccount;
        if ($transaction = self::gateway()->depositFunds($account, $amount)) {
            $deposit = Deposit::create([
                'deposit_type' => 'business',
                'business_id' => $business->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
            $deposit->method()->associate($account);
        }
        return $transaction;
    }

}