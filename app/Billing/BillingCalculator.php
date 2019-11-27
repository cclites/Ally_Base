<?php

namespace App\Billing;

class BillingCalculator
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
     * Calculate the total Ally Fee based on the amount and
     * percentage given, and determine whether to include the
     * fee in the total amount or add the fee to the total.
     *
     * This is the central and only method that should ever
     * be used to make this calculation.
     *
     * @param float $billedAmount
     * @param float $allyPercentage
     * @param bool $feeIncludedInBilledAmount
     * @return float
     */
    public static function calculateAllyFee(float $billedAmount, float $allyPercentage, bool $feeIncludedInBilledAmount = true) : float
    {
        $fee = $feeIncludedInBilledAmount
            ? multiply(divide($billedAmount, add(1, $allyPercentage)), $allyPercentage)
            : multiply($billedAmount, $allyPercentage);

        return (float) round($fee, BillingCalculator::DECIMAL_PLACES, BillingCalculator::ROUNDING_METHOD);
    }

    public static function getCreditCardRate() : float
    {
        return (float) config('ally.credit_card_fee');
    }

    public static function getAmexRate() : float
    {
        return (float) config('ally.amex_card_fee');
    }

    public static function getBankAccountRate() : float
    {
        return (float) config('ally.bank_account_fee');
    }

    public static function getTrustRate() : float
    {
        return (float) config('ally.trust_fee');
    }

    public static function getDefaultRate() : float
    {
        // default to the credit card rate
        return (float) config('ally.credit_card_fee');
    }
}