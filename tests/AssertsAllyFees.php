<?php

namespace Tests;

use App\Billing\BillingCalculator;

trait AssertsAllyFees
{
    /**
     * Helper method that asserts the that the fee for the amount
     * specified matched the expected rate.
     *
     * @param float $paymentAmount
     * @param $expectedRate
     * @param $actualFee
     */
    private function assertAllyFeeRate(float $paymentAmount, $expectedRate, $actualFee): void
    {
        $this->assertEquals(BillingCalculator::calculateAllyFee($paymentAmount, floatval($expectedRate), true), $actualFee);
    }
}