<?php


namespace App\Payments;


use App\Business;
use App\Client;
use App\Scheduling\AllyFeeCalculator;

class MileageExpenseCalculator
{
    const DEFAULT_MILEAGE_RATE = 0.535;

    /**
     * @var \App\Client
     */
    private $client;

    /**
     * @var \App\Business
     */
    private $business;

    private $method;

    /**
     * @var float
     */
    private $mileage;

    public function __construct(Client $client, Business $business, $method, $mileage)
    {
        if ($mileage < 0) $mileage = 0;

        $this->client = $client;
        $this->business = $business;
        $this->method = $method;
        $this->mileage = $mileage;
    }

    public function getMileageRate()
    {
        return $this->business->mileage_rate ?? self::DEFAULT_MILEAGE_RATE;
    }

    public function getCaregiverReimbursement()
    {
        return round($this->mileage * $this->getMileageRate(), 2);
    }

    public function getAllyFee()
    {
        return AllyFeeCalculator::getFee($this->client, $this->method, $this->getCaregiverReimbursement());
    }

    public function getTotalCost()
    {
        return bcadd($this->getAllyFee(), $this->getCaregiverReimbursement(), 2);
    }

}