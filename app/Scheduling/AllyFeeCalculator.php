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
     * @return string
     * @throws \Exception
     */
    public static function getFee(Client $client, $paymentMethod, $paymentAmount)
    {
        if (!in_array($client->client_type, self::$clientTypes)) {
            throw new \Exception('Client type ' . $client->client_type . ' is not supported at this time.');
        }

        $pct = config('ally.bank_account_fee');
        switch($client->client_type) {
            case 'private_pay':
                if (!$paymentMethod) {
                    $paymentMethod = $client->defaultPayment;
                    if (!$paymentMethod) $paymentMethod = new CreditCard();
                }
                if ($paymentMethod instanceof CreditCard) {
                    $pct = config('ally.credit_card_fee');
                }
                // Default is bank account, so no more logic necessary
                break;
            default:
                // Medicaid fee is used for LTCI, VA, and Medicaid.  Expand the switch cases to add more.
                $pct = config('ally.medicaid_fee');
                break;
        }

        return round(bcmul(
            $paymentAmount,
            $pct,
            4
        ), CostCalculator::DEFAULT_SCALE);
    }

}