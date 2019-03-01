<?php
namespace App\Payments;

use App\Caregiver;
use App\Contracts\DepositAggregatorInterface;
use App\Billing\Deposit;
use App\Billing\Gateway\ECSPayment;
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
    }

    /**
     * Get the unsaved version of this aggregated deposit
     *
     * @return \App\Billing\Deposit
     */
    public function getDeposit()
    {
        $deposit = new Deposit([
            'deposit_type' => 'caregiver',
            'caregiver_id' => $this->caregiver->id,
            'amount' => 0,
        ]);

        foreach($this->getShifts() as $shift) {
            $deposit->amount = bcadd($deposit->amount, $shift->costs()->getCaregiverCost(), 2);
        }

        $deposit->load('caregiver');
        return $deposit;
    }

    /**
     * Get all the related shift models of this deposit
     *
     * @return \App\Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getShifts()
    {
        if (!$this->shifts) {
            $this->shifts = Shift::whereAwaitingCaregiverDeposit()
                                 ->whereBetween('checked_in_time', [$this->startDate, $this->endDate])
                                 ->where('caregiver_id', $this->caregiver->id)
                                 ->get();
        }

        return $this->shifts;
    }

    /**
     * Get all the related shift IDs of this deposit
     *
     * @return array
     */
    public function getShiftIds()
    {
        return $this->getShifts()->pluck('id')->toArray();
    }

    /**
     * Process and persist this deposit
     *
     * @return \App\Billing\GatewayTransaction|bool|false
     */
    public function deposit()
    {
        $deposit = $this->getDeposit();
        if ($deposit->amount <= 0) {
            $this->log('Deposit amount < 0');
            return false;
        }

        $account = $this->caregiver->bankAccount;
        if (!$account) {
            $this->log('No bank account found');
            return false;
        }

        try {
            // Process Deposit and Update Status in a Transaction
            DB::beginTransaction();

            // Attempt to update status of all shifts
            foreach($this->getShifts() as $shift) {
                if (!$shift->statusManager()->ackCaregiverDeposit()) {
                    $this->log('Unable to acknowledge caregiver deposits on shift status manager.');
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
                $this->log('Unable to get transaction from Gateway\'s depositFunds method.');
                DB::rollBack();
                return false;
            }

            // Commit database transaction now that deposit has completed
            DB::commit();

            // Save transaction details to the deposit
            $deposit->transaction_id = $transaction->id;
            $deposit->success = $transaction->success;
            $deposit->save();

            return $transaction;
        }
        catch(\Exception $e) {
            $this->log('Exception: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    protected function log($message) {
        $prefix = "[CaregiverDepositAggregator: " . $this->caregiver->id . "] ";
        \Log::debug($prefix.$message);
    }
}