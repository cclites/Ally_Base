<?php
require __DIR__ . "/bootstrap.php";

$clientIds = [2931,8143,8441,3169,8218,2983,3171,3039];
\App\Billing\BusinessInvoice::where('business_id', 37)->where('amount_paid', 0)->delete();
\App\Billing\CaregiverInvoice::whereHas('caregiver', function($q) {
    $q->forBusinesses([37]);
})->where('amount_paid', 0)->where('amount', '>', 0)->delete();
$shifts = \App\Shift::whereIn('client_id', $clientIds)
    ->where('status', 'WAITING_FOR_INVOICE')
    ->get();

DB::beginTransaction();

$store = [];
$payment = \App\Billing\Payment::find(12453);
foreach($shifts as $shift) {
    $store[$shift->id] = [
        'client_rate' => $shift->client_rate,
        'caregiver_rate' => $shift->caregiver_rate,
    ];

    $shift->update([
        'client_rate' => 0,
        'caregiver_rate' => 0,
    ]);

//    Commented out:  No refund on Ally Fees
//    $shift->meta()->delete();
//    $shift->addAmountCharged($payment, 0, 0);
}


// Save shifts to file
file_put_contents(storage_path('saved_rates_for_business_37.txt'), serialize($store));

// Commit transactions
DB::commit();