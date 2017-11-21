<?php
namespace App\Payments;

use App\Business;
use App\Deposit;
use App\Gateway\ECSPayment;
use App\Shift;
use Carbon\Carbon;

class BusinessDepositAggregator
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

        $this->shifts = Shift::where('status', Shift::WAITING_FOR_PAYOUT)
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

    public function deposit()
    {
        $deposit = $this->getDeposit();
        $account = $this->business->bankAccount;
        if (!$account) return false;

        $gateway = new ECSPayment();
        $transaction = $gateway->depositFunds($account, $deposit->amount);
        if ($transaction) {
            $deposit->transaction_id = $transaction->id;
            $deposit->success = $transaction->success;
            $deposit->save();

            // Update shift status
            $shiftIds = $this->getShifts()->pluck('id')->toArray();

            // Associate payment method
            $deposit->method()->associate($account);

            // Attach shifts to deposit
            $deposit->shifts()->attach($shiftIds);
        }

        return $transaction;
    }
}