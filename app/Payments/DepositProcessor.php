<?php
namespace App\Payments;

use App\Business;
use App\Caregiver;
use App\Shift;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;

class DepositProcessor
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * @var \Carbon\Carbon
     */
    private $startDate;

    /**
     * @var \Carbon\Carbon
     */
    private $endDate;

    /**
     * @var int
     */
    private $success = 0;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    public function __construct(Business $business, Carbon $startDate, Carbon $endDate, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->business = $business;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Process all the deposits relating to the business (returns an integer of successful transactions)
     *
     * @return int
     */
    public function process()
    {
        foreach($this->business->caregivers as $caregiver) {
            $this->processCaregiver($caregiver);
        }
        $this->processBusiness();
        return $this->countSuccess();
    }

    /**
     * Return all the deposit models that would be created (does not persist anything)
     *
     * @return \App\Deposit[]
     */
    public function getDepositData()
    {
        $caregivers = $this->business->caregivers->sortBy('nameLastFirst');
        $data = [$this->getBusinessDeposit($this->business)];
        foreach($caregivers as $caregiver) {
            $deposit = $this->getCaregiverDeposit($caregiver);
            if ($deposit->amount > 0) $data[] = $deposit;
        }
        return $data;
    }

    public function getCaregiverDeposit(Caregiver $caregiver, $aggregator = null)
    {
        if (!$aggregator) $aggregator = $this->getCaregiverAggregator($caregiver);
        $deposit = $aggregator->getDeposit();
        return $deposit;
    }

    public function getCaregiverAggregator(Caregiver $caregiver)
    {
        $aggregator = new CaregiverDepositAggregator($caregiver, $this->startDate, $this->endDate);
        return $aggregator;
    }

    public function processCaregiver(Caregiver $caregiver)
    {
        $aggregator = $this->getCaregiverAggregator($caregiver);
        $deposit = $this->getCaregiverDeposit($caregiver, $aggregator);
        if ($deposit->amount > 0) {
            $transaction = false;
            try {
                $transaction = $aggregator->deposit();
                $this->logger->info("Deposited " . $deposit->amount . " to caregiver " . $caregiver->name());
            }
            catch (\Exception $e) {
                $this->logger->error('processCaregiver Error: ' . $e->getMessage());
            }
            if (!$transaction) {
                $this->logger->warning('processCaregiver Warning: Transaction not found for ' . $caregiver->name());
            }
            else {
                $this->success++;
            }
        }
    }

    public function getBusinessDeposit(Business $business, $aggregator = null)
    {
        if (!$aggregator) $aggregator = $this->getBusinessAggregator($business);
        $deposit = $aggregator->getDeposit();
        return $deposit;
    }

    public function getBusinessAggregator(Business $business)
    {
        $aggregator = new BusinessDepositAggregator($business, $this->startDate, $this->endDate);
        return $aggregator;
    }

    public function processBusiness()
    {
        $aggregator = $this->getBusinessAggregator($this->business);
        $deposit = $this->getBusinessDeposit($this->business, $aggregator);
        if ($deposit->amount > 0) {
            $transaction = false;
            try {
                $transaction = $aggregator->deposit();
                $this->logger->info("Deposited " . $deposit->amount . " to business " . $this->business->name);
            }
            catch (\Exception $e) {
                $this->logger->error('processBusiness Error: ' . $e->getMessage());
            }
            if (!$transaction) {
                $this->logger->warning('processBusiness Warning: Transaction not found for ' . $this->business->name);
            }
            else {
                $this->success++;
            }
        }
    }

    public function countSuccess()
    {
        return $this->success;
    }
}