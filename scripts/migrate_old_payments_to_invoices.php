<?php
require __DIR__ . "/bootstrap.php";

use App\Billing\ClientInvoice;
use App\Billing\Payment;

DB::beginTransaction();

////////////////////////////////////
//// Create Client Payers
////////////////////////////////////

// Create a new provider pay payer for each business location
$providerPayers = [];
foreach(\App\Business::all() as $business) {
    $payer = new \App\Billing\Payer([
        'name' => $business->name(),
        'week_start' => 1,
        'address1' => $business->address1,
        'address2' => $business->address2,
        'city' => $business->city,
        'state' => $business->state,
        'zip' => $business->zip,
        'phone_number' => $business->phone1,
        'chain_id' => $business->chain_id,
    ]);
    $payer->paymentMethod()->associate($business);
    $payer->save();
    $providerPayers[$business->id] = $payer;

    // Mark all previous provider pay payments as the new payer id
    Payment::whereNull('client_id')->where('business_id', $business->id)->update(['payer_id' => $payer->id]);
}

// Mark all non-provider pay payments as private payer
Payment::whereNotNull('client_id')->update(['payer_id' => \App\Billing\Payer::PRIVATE_PAY_ID]);


// Assign a balance payer for all existing clients
\App\Client::with('defaultPayment')->chunk(200, function($clients) {
    $clients->each(function(\App\Client $client) {
        global $providerPayers;
        if ($client->getPaymentMethod() instanceof \App\Business) {
            $payer = $providerPayers[$client->business_id];
        } else {
            $payer = null;
        }

        \App\Billing\ClientPayer::create([
            'client_id' => $client->id,
            'payer_id' => $payer->id ?? \App\Billing\Payer::PRIVATE_PAY_ID,
            'effective_start' => '2018-01-01',
            'effective_end' => '9999-12-31',
            'payment_allocation' => \App\Billing\ClientPayer::ALLOCATION_BALANCE,
            'priority' => 1,
        ]);
    });
});

////////////////////////////////////
//// Payments to Invoices
////////////////////////////////////

Payment::with(['client', 'client.payers'])->whereNotNull('client_id')->chunk(200, function($payments) {
    $payments->each(function(Payment $payment) {
        $payer = $payment->payer;

        $invoice = ClientInvoice::create([
            'client_id' => $payment->client_id,
            'payer_id' => $payer->id,
            'name' => ClientInvoice::getNextName($payment->client_id, $payer->id),
            'created_at' => $payment->created_at,
        ]);

        foreach($payment->shifts as $shift) {

            $total = $shift->costs()->getTotalCost();

            if ($shift->costs()->getMileageCost() > 0) {
                $total = subtract($total, $shift->costs()->getMileageCost());
                $expense = _createMileageExpense($shift);
                _assignExpense($invoice, $shift, $expense);
            }

            if ($shift->costs()->getOtherExpenses() > 0) {
                $total = subtract($total, $shift->costs()->getOtherExpenses());
                $expense = _createOtherExpense($shift);
                _assignExpense($invoice, $shift, $expense);
            }


            $item = new \App\Billing\ClientInvoiceItem([
                'rate' => $shift->costs()->getTotalHourlyCost(),
                'units' => $shift->duration(),
                'group' => $shift->getItemGroup(ClientInvoice::class),
                'name' => $shift->getItemName(ClientInvoice::class),
                'total' => $total,
                'amount_due' => $total,
                'date' => $shift->getItemDate(),
            ]);
            $item->associateInvoiceable($shift);
            $invoice->addItem($item);
        }

        if ($invoice->getAmount() != $payment->amount) {
            // Add a manual adjustment
            $diff = subtract($payment->amount, $invoice->getAmount());
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

DB::commit();


////////////////////////////////////
//// DONE:  Functions below
////////////////////////////////////

function _createMileageExpense(\App\Shift $shift)
{
    return \App\Billing\Invoiceable\ShiftExpense::create([
        'shift_id' => $shift->id,
        'name' => 'Mileage',
        'units' => $shift->mileage,
        'rate' => divide($shift->costs()->getMileageCost(), $shift->mileage),
        'ally_fee' => subtract($shift->costs()->getMileageCost(), $shift->costs()->getMileageCost(false))
    ]);
}

function _createOtherExpense(\App\Shift $shift)
{
    return \App\Billing\Invoiceable\ShiftExpense::create([
        'shift_id' => $shift->id,
        'name' => 'Other Expenses',
        'notes' => str_limit($shift->other_expenses_desc, 253, '..'),
        'units' => 1,
        'rate' => $shift->costs()->getOtherExpenses(),
        'ally_fee' => subtract($shift->costs()->getOtherExpenses(), $shift->costs()->getOtherExpenses(false))
    ]);
}

function _assignExpense(ClientInvoice $invoice, \App\Shift $shift, \App\Billing\Invoiceable\ShiftExpense $expense)
{
    $item = new \App\Billing\ClientInvoiceItem([
        'rate' => $expense->getClientRate(),
        'units' => $expense->getItemUnits(),
        'group' => $shift->getItemGroup(ClientInvoice::class),
        'name' => $expense->getItemName(ClientInvoice::class),
        'total' => $total = multiply($expense->getClientRate(), $expense->getItemUnits()),
        'amount_due' => $total,
        'date' => $shift->getItemDate(),
    ]);
    $item->associateInvoiceable($expense);
    $invoice->addItem($item);
}