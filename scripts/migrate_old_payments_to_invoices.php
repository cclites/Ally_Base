<?php
require __DIR__ . "/bootstrap.php";

use App\Billing\ClientInvoice;
use App\Billing\Payment;

DB::beginTransaction();

////////////////////////////////////
//// Create Client Payers
////////////////////////////////////

// TODO: How to create Provider Pay Payers for business chains?  Create a payer for each business location?
// TODO: Create balance payers for all private pay, assign provider pay payer for all "Provider Pay"
// TODO: Add payer_id field to payments, migrate existing business payments to their respective payer ID

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

        if ($invoice->getAmount() < $payment->amount) {
            // Add a manual adjustment
            $diff = bcsub($payment->amount, $invoice->getAmount(), 2);
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