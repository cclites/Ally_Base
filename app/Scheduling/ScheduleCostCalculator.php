<?php

namespace App\Scheduling;


use App\CreditCard;

class ScheduleCostCalculator
{
    /**
     * Number of decimals to use in bcmath calculations
     */
    const DEFAULT_SCALE = 2;

    /**
     * @var \App\BankAccount|\App\CreditCard
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
        return AllyFeeCalculator::getFee(
            $this->client,
            $this->paymentType,
            bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE)
        );
    }

    public function duration()
    {
        return round($this->schedule->duration / 60, 2);
    }

    public function getProviderFee()
    {
        return bcmul($this->duration(), $this->schedule->getProviderFee(), self::DEFAULT_SCALE);
    }

    public function getCaregiverCost()
    {
        return bcmul($this->duration(), $this->schedule->getCaregiverRate(), self::DEFAULT_SCALE);
    }

    public function getTotalCost()
    {
        return bcadd(
            bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE),
            $this->getAllyFee(),
            self::DEFAULT_SCALE
        );
    }
}