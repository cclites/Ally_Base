<?php
namespace App\Payments;

use App\Business;
use App\Contracts\DepositAggregatorInterface;
use App\Deposit;
use App\Gateway\ECSPayment;
use App\Shift;
use Carbon\Carbon;
use DB;

class BusinessDepositAggregator implements DepositAggregatorInterface
{
    protected $business;

    /**
     * @var Shift[]
     */
    protected $shifts;

    public function __construct(Business $business, Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate->setTimezone('UTC');
        $this->endDate = $endDate->setTimezone('UTC');
        $this->business = $business;

        $this->shifts = Shift::whereAwaitingBusinessDeposit()
                ->whereBetween('checked_in_time', [$startDate, $endDate])
                ->where('business_id', $this->business->id)
                ->get();
    }

    public function getDeposit()
    {
        $deposit = new Deposit([
            'deposit_type' => 'business',
            'business_id' => $this->business->id,
            'amount' => 0,
        ]);

        foreach($this->shifts as $shift) {
            $deposit->amount = bcadd($deposit->amount, $shift->costs()->getProviderFee(), 2);
        }

        $deposit->load('business');
        return $deposit;
    }

    public function getShifts()
    {
        return $this->shifts;
    }

    public function getShiftIds()
    {
        return $this->getShifts()->pluck('id')->toArray();
    }

    public function deposit()
    {
        $deposit = $this->getDeposit();
        if ($deposit->amount <= 0) {
            return false;
        }

        $account = $this->business->bankAccount;
        if (!$account) return false;

        try {
            // Process Deposit and Update Status in a Transaction
            DB::beginTransaction();

            // Attempt to update status of all shifts
            foreach($this->getShifts() as $shift) {
                if (!$shift->statusManager()->ackBusinessDeposit()) {
                    DB::rollBack();
                    return false;
                }
            }

            // Attempt to save shifts to the deposit
            $deposit->save();
            $deposit->shifts()->attach($this->getShiftIds());

            // Process Deposit
            $gateway = new ECSPayment();
            if (!$transaction = $gateway->depositFunds($account, $deposit->amount)) {
                DB::rollBack();
                return false;
            }

            // Commit database transaction now that deposit has completed
            DB::commit();

            // Save transaction details to the deposit
            $deposit->transaction_id = $transaction->id;
            $deposit->success = $transaction->success;
            $deposit->save();

            // Associate payment method
            $deposit->method()->associate($account);

            return $transaction;
        }
        catch(\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}