<?php


namespace App\Scheduling;

use App\Client;
use App\CreditCard;

class AllyFeeCalculator
{
    /**
     * Supported client types
     * @var array
     */
    protected static $clientTypes = ['private_pay', 'medicaid', 'LTCI', 'VA'];

    /**
     * @param \App\Client $client
     * @param $paymentMethod
     * @param $paymentAmount
     * @return float
     * @throws \Exception
     */
    public static function getFee(Client $client, $paymentMethod, $paymentAmount)
    {
        if (!in_array($client->client_type, self::$clientTypes)) {
            throw new \Exception('Client type ' . $client->client_type . ' is not supported at this time.');
        }

        $pct = self::getPercentage($client, $paymentMethod);

        return round(
            bcmul(
                $paymentAmount,
                $pct,
                CostCalculator::DEFAULT_SCALE
            ),
            CostCalculator::DECIMAL_PLACES,
            CostCalculator::ROUNDING_METHOD
        );
    }

    /**
     * Return a float of the percentage used for the Ally Fee (5% is returned as 0.05)
     *
     * @return float
     */
    public static function getPercentage(Client $client, $paymentMethod=null)
    {
        if ($client->fee_override !== null) {
            return $client->fee_override;
        }

        $pct = config('ally.bank_account_fee');
        switch($client->client_type) {
            case 'private_pay':
            case 'LTCI':
                if (!$paymentMethod) {
                    $paymentMethod = $client->getPaymentMethod();
                    if (!$paymentMethod) $paymentMethod = new CreditCard();
                }
                if ($paymentMethod instanceof CreditCard) {
                    $pct = config('ally.credit_card_fee');
                    if (strtolower($paymentMethod->type) == 'amex') {
                        $pct = bcadd($pct, '0.01', 2);
                    }
                }
                // Default is bank account, so no more logic necessary
                break;
            default:
                // Medicaid fee is used for LTCI, VA, and Medicaid.  Expand the switch cases to add more.
                $pct = config('ally.medicaid_fee');
                break;
        }
        return $pct;
    }

}