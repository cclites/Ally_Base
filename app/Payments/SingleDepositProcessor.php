<?php
namespace App\Payments;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Caregiver;
use App\Billing\Deposit;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\ECSPayment;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

    public static function depositCaregiver(Caregiver $caregiver, $amount, $adjustment = false, $notes = null, int $chainId)
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
                'chain_id' => $chainId,
            ]);

            $invoice = self::generateCaregiverAdjustmentInvoice($caregiver, $amount, $notes);
            $invoice->addDeposit($deposit, $amount);
        }

        return $transaction;
    }

    public static function generateCaregiverAdjustmentInvoice(Caregiver $caregiver, $amount, $notes = null) : CaregiverInvoice
    {
        $invoice = CaregiverInvoice::create([
            'name' => CaregiverInvoice::getNextName($caregiver->id),
            'caregiver_id' => $caregiver->id,
        ]);

        $invoice->addItem(new CaregiverInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Manual Adjustment',
            'units' => 1,
            'rate' => $amount,
            'total' => $amount,
            'date' => new Carbon(),
            'notes' => Str::limit($notes, 250),
        ]));

        return $invoice;
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
                'chain_id' => $business->chain_id,
            ]);

            $invoice = self::generateBusinessAdjustmentInvoice($business, $amount, $notes);
            $invoice->addDeposit($deposit, $amount);
        }

        return $transaction;
    }

    public static function generateBusinessAdjustmentInvoice(Business $business, $amount, $notes = null) : BusinessInvoice
    {
        $invoice = BusinessInvoice::create([
            'name' => BusinessInvoice::getNextName($business->id),
            'business_id' => $business->id,
        ]);

        $invoice->addItem(new BusinessInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Manual Adjustment',
            'units' => 1,
            'client_rate' => 0,
            'caregiver_rate' => 0,
            'ally_rate' => 0,
            'rate' => $amount,
            'total' => $amount,
            'date' => new Carbon(),
            'notes' => Str::limit($notes, 250),
        ]));

        return $invoice;
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
