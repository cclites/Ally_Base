<?php

namespace App\Traits;

use App\Billing\BillingCalculator;

trait HasAllyFeeTrait
{
    private $allyPctCache;

    /**
     * Get the ally fee in dollars for a specific payment amount
     *
     * @param $paymentAmount
     * @param bool $allyFeeIncluded
     * @return float
     */
    public function getAllyFee($paymentAmount, bool $allyFeeIncluded = false)
    {
        return BillingCalculator::calculateAllyFee(
            $paymentAmount,
            $this->getCachedAllyPercentage(),
            $allyFeeIncluded
        );
    }

    /**
     * Get the rounded ally hourly rate
     *
     * @param $caregiverRate
     * @param $providerFee
     * @return float
     */
    public function getAllyHourlyRate($caregiverRate = null, $providerFee = null)
    {
        $amount = add($caregiverRate, $providerFee, BillingCalculator::DECIMAL_PLACES);
        return $this->getAllyFee($amount, false);
    }

    private function getCachedAllyPercentage()
    {
        return $this->allyPctCache ?? $this->allyPctCache = $this->getAllyPercentage();
    }
}