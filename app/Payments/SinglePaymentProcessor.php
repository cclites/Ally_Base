<?php
namespace App\Payments;

use App\Business;
use App\Client;
use App\Billing\Payment;

/**
 * Class SinglePaymentProcessor
 *
 * Use for manual payment only!  (use PaymentProcessor for automated transactions utilizing shift data)
 *
 * @package App\Payments
 */
class SinglePaymentProcessor
{

    public static function chargeClient(Client $client, $amount, $adjustment = false, $notes = null)
    {
        $method = $client->getPaymentMethod();
        $business = ($client->defaultPayment instanceof Business) ? $client->defaultPayment : null;
        $type = $client->getPaymentType();
        if ($transaction = $method->charge($amount)) {
            $payment = Payment::create([
                'payment_type' => $type,
                'client_id' => ($business) ? null : $client->id,
                'business_id' => ($business) ? $business->id : $client->business_id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'adjustment' => $adjustment,
                'notes' => $notes,
                'success' => $transaction->success,
            ]);
        }
        return $transaction;
    }

    public static function chargeBusiness(Business $business, $amount, $adjustment = false, $notes = null)
    {
        $method = $business->paymentAccount;
        $type = 'ACH-P';
        if ($transaction = $method->charge($amount)) {
            $payment = Payment::create([
                'payment_type' => $type,
                'business_id' => $business->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'adjustment' => $adjustment,
                'notes' => $notes,
                'success' => $transaction->success,
            ]);
        }
        return $transaction;
    }

}