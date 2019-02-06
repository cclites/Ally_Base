<?php
require __DIR__ . "/bootstrap.php";

DB::beginTransaction();

////////////////////////////////////
//// Migrate existing pending shifts
////////////////////////////////////

$statuses = \App\Shifts\ShiftStatusManager::getPendingStatuses();
$shifts = \App\Shift::with(['client', 'client.defaultPayment'])->whereIn('status', $statuses)->get();
$count = 0;
$shifts->each(function (\App\Shift $shift) use (&$count) {
    if ($shift->status === \App\Shift::WAITING_FOR_CHARGE) {
        $shift->status = \App\Shift::WAITING_FOR_INVOICE;
    }
    $shift->client_rate = $shift->costs()->getTotalHourlyCost();
    $count += (int) $shift->save();
});

DB::commit();

$mb = memory_get_peak_usage(true) / 1024 / 1024;
echo "$count rows updated. ${mb}MB memory used.\n";