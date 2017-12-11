<?php
namespace App\Payments;

use App\Business;
use App\Client;
use App\Payment;

/**
 * Class SinglePaymentProcessor
 *
 * Use for manual payment only!  (use PaymentProcessor for automated transactions utilizing shift data)
 *
 * @package App\Payments
 */
class SinglePaymentProcessor
{

    public static function chargeClient(Client $client, $amount)
    {
        $method = $client->getPaymentMethod();
        $business = ($client->defaultPayment instanceof Business) ? $client->defaultPayment : null;
        $type = $client->getPaymentType();
        if ($transaction = $method->charge($amount)) {
            $payment = Payment::create([
                'payment_type' => $type,
                'client_id' => (!$business) ? $client->id : null,
                'business_id' => ($business) ? $business->id : null,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
            $payment->method()->associate($method);
        }
        return $transaction;
    }

    public static function chargeBusiness(Business $business, $amount)
    {
        $method = $business->paymentAccount;
        $type = 'ACH-P';
        if ($transaction = $method->charge($amount)) {
            $payment = Payment::create([
                'payment_type' => $type,
                'business_id' => $business->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => $transaction->success,
            ]);
            $payment->method()->associate($method);
        }
        return $transaction;
    }

}