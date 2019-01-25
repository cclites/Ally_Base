<?php
namespace App\Shifts;

use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Payments\Methods\CreditCard;

/**
 * Class AllyFeeCalculator
 * @package App\Shifts
 */
class AllyFeeCalculator
{
    /**
     * Supported client types
     * @var array
     */
    protected static $clientTypes = ['private_pay', 'medicaid', 'LTCI', 'VA'];

    /**
     * Calculate a one-hour (rounded) rate of the Ally processing fee
     *
     * @param \App\Client $client
     * @param ChargeableInterface|null $paymentMethod
     * @param float $caregiverRate
     * @param float $providerFee
     * @return float
     */
    public static function getHourlyRate(Client $client, ChargeableInterface $paymentMethod = null, $caregiverRate, $providerFee)
    {
        if ($paymentMethod) {
            return $paymentMethod->getAllyHourlyRate($caregiverRate, $providerFee);
        }

        return $client->getAllyHourlyRate($caregiverRate, $providerFee);
    }


    /**
     * Calculate the fee based on a dollar amount (should only be used directly for misc expenses)
     *
     * @param \App\Client $client
     * @param ChargeableInterface|null $paymentMethod
     * @param $paymentAmount
     * @return float
     * @throws \Exception
     */
    public static function getFee(Client $client, ChargeableInterface $paymentMethod = null, $paymentAmount)
    {
        if ($paymentMethod) {
            return $paymentMethod->getAllyFee($paymentAmount);
        }

        return $client->getAllyFee($paymentAmount);
    }

    /**
     * Return a float of the percentage used for the Ally Fee (5% is returned as 0.05)
     *
     * @param \App\Client $client
     * @param \App\Billing\Contracts\ChargeableInterface|null $paymentMethod
     * @return float
     */
    public static function getPercentage(Client $client, ChargeableInterface $paymentMethod = null)
    {
        if ($paymentMethod) {
            return $paymentMethod->getAllyPercentage();
        }

        return $client->getAllyPercentage();
    }

}