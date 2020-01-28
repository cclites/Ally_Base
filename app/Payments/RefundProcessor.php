<?php

namespace App\Payments;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Gateway\ECSPayment;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use Carbon\Carbon;

class RefundProcessor
{
    /**
     * @var \App\Billing\GatewayTransaction
     */
    private $transaction;
    /**
     * @var \App\Billing\Gateway\ECSPayment
     */
    private $ECSPayment;

    public function __construct(GatewayTransaction $transaction, ECSPayment $ECSPayment = null)
    {
        $this->transaction = $transaction;
        $this->ECSPayment = $ECSPayment ?? new ECSPayment();
    }

    /**
     * Refund the transaction a certain amount
     *
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
     */
    public function refund($amount, $notes = '')
    {
        if ($amount > $this->transaction->amount) {
            throw new \Exception('The refund amount cannot be greater than the transaction amount');
        }

        if (!$method = $this->transaction->method) {
            throw new \Exception('No transaction method found.  Cannot refund.');
        }

        $payment = $this->transaction->payment;

        if ($transaction = $method->refund($this->transaction, $amount)) {

            // Convert a positive refund to a negative payment
            $amount = $amount * -1;

            $payment = Payment::create([
                'client_id' => optional($payment)->client_id,
                'business_id' => optional($payment)->business_id,
                'payment_type' => optional($payment)->payment_type,
                'payment_method_type' => optional($payment)->payment_method_type,
                'payment_method_id' => optional($payment)->payment_method_id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'success' => true,
                'business_allotment' => 0,
                'caregiver_allotment' => 0,
                'system_allotment' => 0,
                'adjustment' => true,
                'notes' => $notes,
            ]);
            if (!$payment) {
                echo "Payment could not be recorded for refund.\n";
            }

            if ($client = $payment->client) {
                $invoice = ClientInvoice::create([
                    'name' => ClientInvoice::getNextName($client->id),
                    'client_id' => $client->id,
                ]);
                $invoice->addItem(new ClientInvoiceItem([
                    'group' => 'Adjustments',
                    'name' => 'Refund',
                    'units' => 1,
                    'rate' => $amount,
                    'total' => $amount,
                    'amount_due' => $amount,
                    'date' => new Carbon(),
                    'notes' => Str::limit($notes, 250),
                ]));
                $invoice->addPayment($payment, $amount);
            }
            // TODO: Need to figure out how to record invoices for refunds to business entities

        }

        return $transaction;
    }

}