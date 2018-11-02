<?php
namespace App\Shifts;

class Rates
{
    /**
     * @var float
     */
    public $caregiver_rate;

    /**
     * @var float
     */
    public $provider_fee;

    /**
     * @var bool
     */
    public $fixed_rates = false;

    /**
     * Rates constructor.
     * @param float $caregiver_rate
     * @param float $provider_fee
     * @param bool $fixed_rates
     */
    public function __construct(float $caregiver_rate, float $provider_fee = 0.0, bool $fixed_rates = false)
    {
        $this->caregiver_rate = $caregiver_rate;
        $this->provider_fee = $provider_fee;
        $this->fixed_rates = $fixed_rates;
    }

    /**
     * @return float
     */
    public function getClientRate()
    {
        return round(bcadd($this->caregiver_rate, $this->provider_fee, 4), 2);
    }

    /**
     * @param float $rate
     * @param float|null $caregiver_rate
     * @throws \Exception
     */
    public function setClientRate(float $rate, float $caregiver_rate = null)
    {
        if ($caregiver_rate === null) {
            $caregiver_rate = $this->caregiver_rate;
        }

        if ($caregiver_rate === null) {
            throw new \Exception('caregiver_rate must be set before client_rate');
        }

        $this->provider_fee = round(bcsub($rate, $caregiver_rate, 4), 2);
    }

}
