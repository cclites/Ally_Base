<?php

require __DIR__ . "/bootstrap.php";

DB::beginTransaction();

$count = 0;
$shifts = \App\Shift::with(['client', 'client.defaultPayment'])->where('status', \App\Shift::WAITING_FOR_CHARGE)->get();
$shifts->each(function (\App\Shift $shift) use (&$count) {
    $shift->status = \App\Shift::WAITING_FOR_INVOICE;
    $shift->client_rate = $shift->costs()->getTotalHourlyCost();
    $count += (int) $shift->save();
});

DB::commit();

$mb = memory_get_peak_usage(true) / 1024 / 1024;

echo "$count rows updated. ${mb}MB memory used.\n";