<?php


namespace App\Traits;


trait HasAllyFeeTrait
{
    /**
     * Get the ally fee in dollars for a specific payment amount
     *
     * @param $paymentAmount
     * @return float
     */
    public function getAllyFee($paymentAmount)
    {
        $amount = bcmul($paymentAmount, $this->getAllyPercentage(), 2);
        return (float) $amount;
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
        $amount = bcadd($caregiverRate, $providerFee, 2);
        return $this->getAllyFee($amount);
    }
}