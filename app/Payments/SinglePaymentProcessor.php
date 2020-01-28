<?php

namespace App\Payments;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Business;
use App\Client;
use App\Billing\Payment;
use Carbon\Carbon;

/**
 * Class SinglePaymentProcessor
 *
 * Use for manual payment only!  (use PaymentProcessor for automated transactions utilizing shift data)
 *
 * @package App\Payments
 */
class SinglePaymentProcessor
{
    /**
     * Create Payment for a Client.
     *
     * @param Client $client
     * @param $amount
     * @param bool $adjustment
     * @param null $notes
     * @return mixed
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
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
                'notes' => Str::limit($notes, 250),
                'success' => $transaction->success,
            ]);
            $payment->setPaymentMethod($method);
            $payment->save();

            $invoice = ClientInvoice::create([
                'name' => ClientInvoice::getNextName($client->id),
                'client_id' => $client->id,
            ]);
            $invoice->addItem(new ClientInvoiceItem([
                'group' => 'Adjustments',
                'name' => 'Manual Adjustment',
                'units' => 1,
                'rate' => $amount,
                'total' => $amount,
                'amount_due' => $amount,
                'date' => new Carbon(),
                'notes' => Str::limit($notes, 250),
            ]));
            $invoice->addPayment($payment, $amount);
        }
        return $transaction;
    }

    // TODO: How can we create payment invoices for businesses???
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