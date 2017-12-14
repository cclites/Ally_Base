<?php
namespace App\Payments;

use App\Caregiver;
use App\Contracts\DepositAggregatorInterface;
use App\Deposit;
use App\Gateway\ECSPayment;
use App\Shift;
use Carbon\Carbon;
use DB;

class CaregiverDepositAggregator implements DepositAggregatorInterface
{
    protected $caregiver;

    /**
     * @var Shift[]
     */
    protected $shifts;

    public function __construct(Caregiver $caregiver, Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate->setTimezone('UTC');
        $this->endDate = $endDate->setTimezone('UTC');
        $this->caregiver = $caregiver;

        $this->shifts = Shift::whereAwaitingCaregiverDeposit()
                ->whereBetween('checked_in_time', [$startDate, $endDate])
                ->where('caregiver_id', $this->caregiver->id)
                ->get();
    }

    public function getDeposit()
    {
        $deposit = new Deposit([
            'deposit_type' => 'caregiver',
            'caregiver_id' => $this->caregiver->id,
            'amount' => 0,
        ]);

        foreach($this->shifts as $shift) {
            $deposit->amount = bcadd($deposit->amount, $shift->costs()->getCaregiverCost(), 2);
        }

        $deposit->load('caregiver');
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

        $account = $this->caregiver->bankAccount;
        if (!$account) return false;

        try {
            // Process Deposit and Update Status in a Transaction
            DB::beginTransaction();

            // Attempt to update status of all shifts
            foreach($this->getShifts() as $shift) {
                if (!$shift->statusManager()->ackCaregiverDeposit()) {
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