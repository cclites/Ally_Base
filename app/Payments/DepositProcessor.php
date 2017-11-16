<?php
namespace App\Payments;

use App\Business;
use App\Caregiver;
use App\Shift;
use Carbon\Carbon;

class DepositProcessor
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * All processed shift IDs
     *
     * @var array
     */
    protected $shifts = [];

    /**
     * Shift ids belonging to failed caregiver deposits
     *
     * @var array
     */
    protected $failedCaregiverShifts = [];

    /**
     * @var \Carbon\Carbon
     */
    private $startDate;
    /**
     * @var \Carbon\Carbon
     */
    private $endDate;

    public function __construct(Business $business, Carbon $startDate, Carbon $endDate)
    {
        $this->business = $business;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function process()
    {
        foreach($this->business->caregivers as $caregiver) {
            $this->processCaregiver($caregiver);
        }
        $this->processBusiness();
        $this->updateShiftStatuses();
    }

    public function processCaregiver(Caregiver $caregiver)
    {
        $aggregator = new CaregiverDepositAggregator($caregiver, $this->startDate, $this->endDate);
        $deposit = $aggregator->getDeposit();
        // TEST
        echo "Deposit to caregiver " . $caregiver->name() . " : " . $deposit->amount . "\n";
        return;
        if ($deposit->amount > 0) {
            $transaction = false;
            $this->shifts = array_merge($this->shifts, $aggregator->getShiftIds());
            try {
                $transaction = $aggregator->deposit();
                echo "Deposited " . $deposit->amount . " to caregiver " . $caregiver->name() . "\n";
            }
            catch (\Exception $e) {}
            if (!$transaction) {
                $this->failedCaregiverShifts = array_merge($this->shifts, $aggregator->getShiftIds());
            }
        }
    }

    public function processBusiness()
    {
        $aggregator = new BusinessDepositAggregator($this->business, $this->startDate, $this->endDate);
        $deposit = $aggregator->getDeposit();
        // TEST
        echo "Business " . $this->business->name . " Deposit: " . $deposit->amount . "\n";
        return;
        if ($deposit->amount > 0) {
            $transaction = false;
            try {
                $transaction = $aggregator->deposit();
                echo "Deposited " . $deposit->amount . " to business " . $this->business->name . "\n";
            }
            catch (\Exception $e) {}
            if (!$transaction) {
                // DO SOMETHING WITH FAILED BUSINESS TRANSACTION
            }
        }
    }

    public function updateShiftStatuses()
    {
        Shift::whereIn('id', $this->shifts)->update(['status' => Shift::PAID]);
        Shift::whereIn('id', $this->failedCaregiverShifts)->update(['status' => Shift::PAID_BUSINESS_ONLY]);
    }
}