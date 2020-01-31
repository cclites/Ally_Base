<?php
require __DIR__ . "/bootstrap.php";

use App\Billing\ClientInvoice;
use App\Billing\Deposit;
use App\Billing\Payment;
use Illuminate\Support\Str;

DB::beginTransaction();

////////////////////////////////////
//// Client Payments to Invoices
////////////////////////////////////

Payment::with(['paymentMethod', 'client', 'client.payers'])->whereNotNull('client_id')->chunk(500, function($payments) {
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
                'date' => $payment->created_at,
                'rate' => $diff,
                'units' => 1,
                'group' => 'Adjustments',
                'name' => 'Manual Adjustment',
                'total' => $diff,
                'amount_due' => $diff,
                'notes' => Str::limit($payment->notes, 253, '..'),
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

Payment::with(['shifts', 'shifts.client', 'shifts.client.payers'])->whereNull('client_id')->chunk(500, function($payments) {
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
//// Caregiver deposits to invoices
////////////////////////////////////

Deposit::with(['caregiver', 'shifts', 'shifts.expenses'])->whereNotNull('caregiver_id')->chunk(500, function($deposits) {
    $deposits->each(function(Deposit $deposit) {
        $invoice = \App\Billing\CaregiverInvoice::create([
            'name' => \App\Billing\CaregiverInvoice::getNextName($deposit->caregiver_id),
            'caregiver_id' => $deposit->caregiver_id,
            'created_at' => $deposit->created_at,
        ]);

        foreach($deposit->shifts as $shift) {
            /** @var \App\Billing\Invoiceable\ShiftExpense $expense */
            foreach($shift->expenses as $expense) {
                $item = new \App\Billing\CaregiverInvoiceItem([
                    'group' => $expense->getItemGroup(\App\Billing\CaregiverInvoice::class),
                    'name' => $expense->getItemName(\App\Billing\CaregiverInvoice::class),
                    'units' => $units = $expense->getItemUnits(),
                    'rate' => $rate = $expense->getCaregiverRate(),
                    'total' => multiply($rate, $units),
                    'date' => $shift->getItemDate(),
                ]);
                $item->associateInvoiceable($expense);
                $invoice->addItem($item);
            }

            $item = new \App\Billing\CaregiverInvoiceItem([
                'group' => $shift->getItemGroup(\App\Billing\CaregiverInvoice::class),
                'name' => $shift->getItemName(\App\Billing\CaregiverInvoice::class),
                'units' => $shift->duration(),
                'rate' => $shift->caregiver_rate,
                'total' => $shift->costs()->getCaregiverCost(false),
                'date' => $shift->getItemDate(),
            ]);
            $item->associateInvoiceable($shift);
            $invoice->addItem($item);
        }

        if ($invoice->getAmount() != $deposit->amount) {
            // Add a manual adjustment
            $diff = subtract($deposit->amount, $invoice->getAmount());
            $item = new \App\Billing\CaregiverInvoiceItem([
                'rate' => $diff,
                'units' => 1,
                'group' => 'Adjustments',
                'name' => 'Manual Adjustment',
                'total' => $diff,
                'notes' => Str::limit($deposit->notes, 253, '..'),
                'date' => $deposit->created_at,
            ]);
            $invoice->addItem($item);
        }

        $invoice->addDeposit($deposit, $deposit->amount);
    });
});
echo("Line 203\n");


////////////////////////////////////
//// Business deposits to invoices
////////////////////////////////////

Deposit::with(['business', 'shifts'])->whereNotNull('business_id')->chunk(500, function($deposits) {
    $deposits->each(function(Deposit $deposit) {
        $invoice = \App\Billing\BusinessInvoice::create([
            'name' => \App\Billing\BusinessInvoice::getNextName($deposit->business_id),
            'business_id' => $deposit->business_id,
            'created_at' => $deposit->created_at,
        ]);

        foreach($deposit->shifts as $shift) {
            $item = new \App\Billing\BusinessInvoiceItem([
                'group' => $shift->getItemGroup(\App\Billing\BusinessInvoice::class),
                'name' => $shift->getItemName(\App\Billing\BusinessInvoice::class),
                'units' => $shift->duration(),
                'client_rate' => $clientRate = $shift->costs()->getTotalHourlyCost(),
                'caregiver_rate' => $shift->caregiver_rate,
                'ally_rate' => subtract($clientRate, add($shift->caregiver_rate, $shift->provider_fee)),
                'rate' => $shift->provider_fee,
                'total' => $shift->costs()->getProviderFee(),
                'date' => $shift->getItemDate(),
            ]);
            $item->associateInvoiceable($shift);
            $invoice->addItem($item);
        }

        if ($invoice->getAmount() != $deposit->amount) {
            // Add a manual adjustment
            $diff = subtract($deposit->amount, $invoice->getAmount());
            $item = new \App\Billing\BusinessInvoiceItem([
                'rate' => $diff,
                'client_rate' => 0,
                'caregiver_rate' => 0,
                'ally_rate' => 0,
                'units' => 1,
                'group' => 'Adjustments',
                'name' => 'Manual Adjustment',
                'total' => $diff,
                'notes' => Str::limit($deposit->notes, 253, '..'),
                'date' => $deposit->created_at,
            ]);
            $invoice->addItem($item);
        }

        $invoice->addDeposit($deposit, $deposit->amount);
    });
});
echo("Line 204\n");

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
        'rate' => divide($shift->costs()->getMileageCost(false), $shift->mileage, 4),
        'ally_fee' => subtract($shift->costs()->getMileageCost(), $shift->costs()->getMileageCost(false)),
    ]);
}

function _createOtherExpense(\App\Shift $shift)
{
    return \App\Billing\Invoiceable\ShiftExpense::create([
        'shift_id' => $shift->id,
        'name' => 'Other Expenses',
        'notes' => Str::limit($shift->other_expenses_desc, 253, '..'),
        'units' => 1,
        'rate' => $shift->costs()->getOtherExpenses(false),
        'ally_fee' => subtract($shift->costs()->getOtherExpenses(), $shift->costs()->getOtherExpenses(false)),
    ]);
}

function _assignExpense(ClientInvoice $invoice, \App\Shift $shift, \App\Billing\Invoiceable\ShiftExpense $expense)
{
    $item = new \App\Billing\ClientInvoiceItem([
        'rate' => add($expense->getClientRate(), $expense->getAllyRate()),
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