<?php


namespace App\Traits;


use App\Shifts\CostCalculator;

trait HasAllyFeeTrait
{
    /**
     * Get the ally fee in dollars for a specific payment amount
     *
     * @param $paymentAmount
     * @param bool $allyFeeIncluded
     * @return float
     */
    public function getAllyFee($paymentAmount, bool $allyFeeIncluded = false)
    {
        $allyPct = $this->getAllyPercentage();
        $amount = $allyFeeIncluded
            ? divide($paymentAmount, multiply(add(1, $allyPct), $allyPct))
            : multiply($paymentAmount, $allyPct);

        return (float) round($amount, CostCalculator::DECIMAL_PLACES, CostCalculator::ROUNDING_METHOD);
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
        $amount = add($caregiverRate, $providerFee, CostCalculator::DECIMAL_PLACES);
        return $this->getAllyFee($amount, false);
    }
}