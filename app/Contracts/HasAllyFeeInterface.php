<?php


namespace App\Contracts;


interface HasAllyFeeInterface
{
    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage();

    /**
     * Get the ally fee in dollars for a specific payment amount
     *
     * @param $paymentAmount
     * @return float
     */
    public function getAllyFee($paymentAmount);

    /**
     * Get the rounded ally hourly rate
     *
     * @param $caregiverRate
     * @param $providerFee
     * @return float
     */
    public function getAllyHourlyRate($caregiverRate = null, $providerFee = null);
}