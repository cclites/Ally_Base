<?php
require __DIR__ . "/bootstrap.php";

use App\Billing\ClientInvoice;
use App\Billing\Payment;

DB::beginTransaction();

////////////////////////////////////
//// Create Client Payers
////////////////////////////////////



////////////////////////////////////
//// Client Payments to Invoices
////////////////////////////////////

Payment::whereNotNull('client_id')->chunk(100, function($payments) {
    $payments->each(function(Payment $payment) {
        $invoice = ClientInvoice::create([
            'client_id' => $payment->client_id,
            'payer_id' => \App\Billing\Payer::PRIVATE_PAY_ID,
            'name' => ClientInvoice::getNextName($payment->client_id, \App\Billing\Payer::PRIVATE_PAY_ID),
        ]);

        foreach($payment->shifts as $shift) {
            $item = new \App\Billing\ClientInvoiceItem([
                'rate' => $shift->costs()->getTotalHourlyCost(),
                'units' => $shift->duration(),
                'group' => $shift->getItemGroup(ClientInvoice::class),
                'name' => $shift->getItemName(ClientInvoice::class),
                'total' => $shift->costs()->getTotalCost(),
                'amount_due' => $shift->costs()->getTotalCost(),
                'date' => $shift->getItemDate(),
            ]);
            $item->associateInvoiceable($shift);
            $invoice->addItem($item);
        }

        if ($invoice->getAmount() < $payment->amount) {
            // Add a manual adjustment
            $diff = bcsub($invoice->getAmount(), $payment->amount, 2);
            $item = new \App\Billing\ClientInvoiceItem([
                'rate' => $diff,
                'units' => 1,
                'group' => 'Adjustments',
                'name' => 'Manual Adjustment',
                'total' => $diff,
                'amount_due' => $diff,
                'notes' => str_limit($payment->notes, 253, '..'),
            ]);
            $invoice->addItem($item);
        }

        $invoice->addPayment($payment, $payment->amount);
    });
});

/////////////////////////////////////
//// Provider Pay Payments to Invoices
////////////////////////////////////



DB::commit();