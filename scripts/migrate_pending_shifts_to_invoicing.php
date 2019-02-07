<?php
require __DIR__ . "/bootstrap.php";

DB::beginTransaction();

////////////////////////////////////
//// Migrate existing pending shifts
////////////////////////////////////

$statuses = \App\Shifts\ShiftStatusManager::getPendingStatuses() + [\App\Shift::WAITING_FOR_CHARGE];
$shifts = \App\Shift::with(['client', 'client.defaultPayment'])->whereIn('status', $statuses)->get();
$count = 0;
$shifts->each(function (\App\Shift $shift) use (&$count) {
    $status = $shift->status === \App\Shift::WAITING_FOR_CHARGE ? \App\Shift::WAITING_FOR_INVOICE : $shift->status;
    $rate = $shift->costs()->getTotalHourlyCost();
    $count += \DB::table('shifts')->where('id', $shift->id)->update([
        'client_rate' => $rate,
        'status' => $status,
    ]);
});

DB::commit();

$mb = memory_get_peak_usage(true) / 1024 / 1024;
echo "$count rows updated. ${mb}MB memory used.\n";