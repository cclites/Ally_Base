<?php
namespace App\Scheduling;

class CostCalculator
{
    /**
     * Number of decimals to use in bcmath calculations
     */
    const DEFAULT_SCALE = 4;

    /**
     * Number of decimals to use in rounding
     */
    const DECIMAL_PLACES = 2;

    /**
     * Rounding methodology
     */
    const ROUNDING_METHOD = PHP_ROUND_HALF_UP;

    /**
     * @var \App\BankAccount|\App\CreditCard
     */
    protected $paymentType;

    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * @var \App\Shift
     */
    protected $shift;

    public function __construct($shift)
    {
        $this->shift = $shift;
        $this->client = $this->shift->client;
        if (!$this->client) throw new \Exception('Shift does not have a client, cannot calculate costs.');
    }

    public function setPaymentType($method)
    {
        $this->paymentType = $method;
        return $this;
    }

    public function getAllyFee()
    {
        $amount = bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE);
        return AllyFeeCalculator::getFee($this->client, $this->paymentType, $amount);
    }

    public function getProviderFee()
    {
        if ($this->shift->all_day) {
            return round($this->shift->provider_fee, self::DEFAULT_SCALE);
        }
        return round(
            bcmul($this->shift->duration(), $this->shift->provider_fee, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    public function getCaregiverCost()
    {
        if ($this->shift->all_day) {
            return round($this->shift->caregiver_rate, self::DEFAULT_SCALE);
        }
        return round(
            bcmul($this->shift->duration(), $this->shift->caregiver_rate, self::DEFAULT_SCALE),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }

    public function getTotalCost()
    {
        return round(
            bcadd(
                bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE),
                $this->getAllyFee(),
                self::DEFAULT_SCALE
            ),
            self::DECIMAL_PLACES,
            self::ROUNDING_METHOD
        );
    }
}