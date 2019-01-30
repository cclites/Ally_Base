<?php
require __DIR__ . "/bootstrap.php";

use App\Billing\ClientInvoice;
use App\Billing\Payment;

DB::beginTransaction();

////////////////////////////////////
//// Run preparation for each business chain
////////////////////////////////////

$providerPayers = [];

/** @var \App\BusinessChain $chain */
foreach(\App\BusinessChain::all() as $chain) {

    $businessIds = $chain->businesses()->pluck('id')->toArray();

    ////////////////////////////////////
    //// Create a default service
    ////////////////////////////////////

    $service = \App\Billing\Service::create([
        'name' => 'Caregiver Service',
        'code' => '',
        'default' => true,
        'chain_id' => $chain->id,
    ]);

    ////////////////////////////////////
    //// Update shifts to use that service
    ////////////////////////////////////

    \App\Shift::whereIn('business_id', $businessIds)->update(['service_id' => $service->id]);
    echo("Line 36\n");

    ////////////////////////////////////
    //// Update schedules to use that service
    ////////////////////////////////////

    \App\Schedule::whereIn('business_id', $businessIds)->update(['service_id' => $service->id]);
    echo("Line 43\n");

    /////////////////////////////////////
    //// Create a new provider payer
    ////////////////////////////////////

    $payer = \App\Billing\Payer::create([
        'name' => $chain->name,
        'week_start' => 1,
        'address1' => $chain->address1,
        'address2' => $chain->address2,
        'city' => $chain->city,
        'state' => $chain->state,
        'zip' => $chain->zip,
        'phone_number' => $chain->phone1,
        'chain_id' => $chain->id,
        'payment_method_type' => 'businesses', // Important
    ]);

    // Update all previous provider payer payments with the payment method
    foreach($businessIds as $businessId) {
        $providerPayers[$businessId] = $payer;
        Payment::whereNull('client_id')->where('business_id', $businessId)->update([
            'payment_method_type' => 'businesses',
            'payment_method_id' => $businessId,
        ]);
    }
    echo("Line 70\n");

}

////////////////////////////////////
//// Update all previous private pay payments with their payment method
////////////////////////////////////

DB::statement("
UPDATE payments p
INNER JOIN gateway_transactions t ON p.transaction_id = t.id
SET p.payment_method_type = t.method_type, p.payment_method_id = t.method_id
WHERE p.client_id IS NOT NULL
");
echo("Line 84\n");

////////////////////////////////////
//// Assign Balance Payers to Clients
////////////////////////////////////

$rows = DB::affectingStatement("
INSERT INTO client_payers (client_id, payer_id, effective_start, effective_end, payment_allocation)
SELECT DISTINCT c.id, p.id, '2018-01-01', '9999-12-31', 'balance' FROM clients c
INNER JOIN businesses b ON b.id = c.business_id
INNER JOIN payers p ON p.chain_id = b.chain_id
WHERE c.default_payment_type = 'businesses'
");
echo "$rows affected by provider pay setting\n";

var_dump(DB::select('SELECT count(*) FROM client_payers'));

$rows = DB::affectingStatement("
INSERT INTO client_payers (client_id, payer_id, effective_start, effective_end, payment_allocation)
SELECT id, '0', '2018-01-01', '9999-12-31', 'balance' FROM clients WHERE default_payment_type != 'businesses' OR default_payment_type IS NULL
");
echo "$rows affected by private pay setting\n";
echo("Line 104\n");

var_dump(DB::select('SELECT count(*) FROM client_payers'));

////////////////////////////////////
//// Client Payments to Invoices
////////////////////////////////////

Payment::with(['paymentMethod', 'client', 'client.payers'])->whereNotNull('client_id')->chunk(200, function($payments) {
    $payments->each(function(Payment $payment) {

        if (!$payment->client->payers->first()) {
            dd($payment->client->toArray());
        }

        $invoice = ClientInvoice::create([
            'client_id' => $payment->client_id,
            'client_payer_id' => $payment->client->payers->first()->id,
            'name' => ClientInvoice::getNextName($payment->client_id),
            'created_at' => $payment->created_at,
        ]);

        foreach($payment->shifts as $shift) {
            $item = _assignShift($invoice, $shift);
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
echo("Line 139\n");

////////////////////////////////////
//// Provider Pay Payments to Invoices (One per client)
////////////////////////////////////

$missedAmounts = [];

Payment::with(['shifts', 'shifts.client', 'shifts.client.payers'])->whereNull('client_id')->chunk(200, function($payments) {
    $payments->each(function(Payment $payment) {
        $groupedShifts = $payment->shifts->groupBy('client_id');

        $totalInvoiced = 0;
        foreach($groupedShifts as $clientId => $shifts) {
            $payer = $shifts->first()->client->payers->first();
            $invoice = ClientInvoice::create([
                'client_id' => $clientId,
                'client_payer_id' => $payer->id,
                'name' => ClientInvoice::getNextName($clientId),
                'created_at' => $payment->created_at,
            ]);

            foreach($shifts as $shift) {
                $item = _assignShift($invoice, $shift);
            }

            $invoice->addPayment($payment, $invoice->getAmountDue());
            $totalInvoiced = add($totalInvoiced, $invoice->getAmount());
        }

        if ($totalInvoiced != $payment->amount) {
            $diff = subtract($payment->amount, $totalInvoiced);
            $missedAmounts[] = [
                'payment_id' => $payment->id,
                'diff' => $diff,
                'shift_count' => $payment->shifts->count(),
            ];

//            // Add a manual adjustment invoice
//            $invoice = ClientInvoice::create([
//                'client_id' => null, // THIS CAN'T BE CREATED
//                'payer_id' => $payer->id,
//                'name' => ClientInvoice::getNextName($payment->client_id, $payer->id),
//                'created_at' => $payment->created_at,
//            ]);
//
//            $item = new \App\Billing\ClientInvoiceItem([
//                'rate' => $diff,
//                'units' => 1,
//                'group' => 'Adjustments',
//                'name' => 'Manual Adjustment',
//                'total' => $diff,
//                'amount_due' => $diff,
//                'notes' => str_limit($payment->notes, 253, '..'),
//            ]);
//            $invoice->addItem($item);
        }

    });
});
echo("Line 199\n");

file_put_contents(base_path('missed_amounts.serialized'), serialize($missedAmounts));
echo("Line 202\n");


////////////////////////////////////
//// Migrate client caregiver rates
////////////////////////////////////

\App\Client::has('caregivers')->with(['caregivers', 'defaultPayment'])->chunk(200, function($clients) {
    $clients->each(function(\App\Client $client) {
        foreach($client->caregivers as $caregiver) {
            $paymentMethod = $client->getPaymentMethod() ?? new \App\Billing\Payments\Methods\CreditCard();
            \App\Billing\ClientRate::create([
                'client_id' => $client->id,
                'caregiver_id' => $caregiver->id,
                'client_hourly_rate' => multiply(add($caregiver->pivot->caregiver_hourly_rate, $caregiver->pivot->provider_hourly_fee), add(1, $paymentMethod->getAllyPercentage())),
                'caregiver_hourly_rate' => $caregiver->pivot->caregiver_hourly_rate ?? 0,
                'client_fixed_rate' => multiply(add($caregiver->pivot->caregiver_fixed_rate, $caregiver->pivot->provider_fixed_fee), add(1, $paymentMethod->getAllyPercentage())),
                'caregiver_fixed_rate' => $caregiver->pivot->caregiver_fixed_rate ?? 0,
                'effective_start' => '2018-01-01',
                'effective_end' => '9999-12-31',
            ]);
        }
    });
});
echo("Line 226\n");


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
        'ally_fee' => subtract($shift->costs()->getMileageCost(), $shift->costs()->getMileageCost(false)),
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
        'ally_fee' => subtract($shift->costs()->getOtherExpenses(), $shift->costs()->getOtherExpenses(false)),
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

function _assignShift(ClientInvoice $invoice, \App\Shift $shift): \App\Billing\ClientInvoiceItem
{
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
    return $item;
}