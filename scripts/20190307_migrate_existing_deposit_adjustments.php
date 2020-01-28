<?php

require __DIR__ . '/bootstrap.php';

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Queries\DepositQuery;
use Carbon\Carbon;


$query = app(DepositQuery::class);
$deposits = $query->hasAmountAvailable()->where('notes', 'NOT LIKE', 'BANK ERROR%')->get();

DB::beginTransaction();

/** @var \App\Billing\Deposit $deposit */
foreach($deposits as $deposit) {
    $amount = $deposit->amount;
    $notes = $deposit->notes;
    if ($deposit->deposit_type === "business") {
        $business = \App\Business::find($deposit->business_id);
        $invoice = BusinessInvoice::create([
            'name' => BusinessInvoice::getNextName($business->id),
            'business_id' => $business->id,
        ]);
        $invoice->addItem(new BusinessInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Manual Adjustment',
            'units' => 1,
            'client_rate' => 0,
            'caregiver_rate' => 0,
            'ally_rate' => 0,
            'rate' => $amount,
            'total' => $amount,
            'date' => new Carbon(),
            'notes' => Str::limit($notes, 250),
        ]));
        $invoice->addDeposit($deposit, $amount);
    } else {
        $caregiver = \App\Caregiver::find($deposit->caregiver_id);
        $invoice = CaregiverInvoice::create([
            'name' => CaregiverInvoice::getNextName($caregiver->id),
            'caregiver_id' => $caregiver->id,
        ]);
        $invoice->addItem(new CaregiverInvoiceItem([
            'group' => 'Adjustments',
            'name' => 'Manual Adjustment',
            'units' => 1,
            'rate' => $amount,
            'total' => $amount,
            'date' => new Carbon(),
            'notes' => Str::limit($notes, 250),
        ]));
        $invoice->addDeposit($deposit, $amount);
    }
}

DB::commit();