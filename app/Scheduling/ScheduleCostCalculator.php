<?php
namespace App\Scheduling;

use App\Billing\BillingCalculator;

class ScheduleCostCalculator
{
    /**
     * Number of decimals to use in bcmath calculations
     */
    const DEFAULT_SCALE = 2;

    /**
     * @var \App\Billing\Payments\Methods\BankAccount|\App\Billing\Payments\Methods\CreditCard
     */
    protected $paymentType;

    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * @var \App\Schedule
     */
    protected $schedule;

    /**
     * Supported client types
     * @var array
     */
    protected $clientTypes = ['private_pay', 'medicaid', 'LTCI', 'VA'];

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
        $this->client = $schedule->client;
        if (!$this->client) throw new \Exception('Schedule does not have a client, cannot calculate costs.');
    }

    public function setPaymentType($method)
    {
        $this->paymentType = $method;
        return $this;
    }

    public function getAllyFee()
    {
        return AllyFeeCalculator::getHourlyRate(
            $this->client,
            $this->paymentType,
            $this->getProviderFee(),
            $this->getCaregiverCost()
        );
    }

    public function duration()
    {
        return round($this->schedule->duration / 60, 2);
    }

    public function getProviderFee()
    {
        return bcmul($this->duration(), $this->schedule->getProviderFee(), BillingCalculator::DEFAULT_SCALE);
    }

    public function getCaregiverCost()
    {
        return bcmul($this->duration(), $this->schedule->getCaregiverRate(), BillingCalculator::DEFAULT_SCALE);
    }

    public function getTotalCost()
    {
        return bcadd(
            bcadd($this->getProviderFee(), $this->getCaregiverCost(), BillingCalculator::DEFAULT_SCALE),
            $this->getAllyFee(),
            BillingCalculator::DEFAULT_SCALE
        );
    }
}