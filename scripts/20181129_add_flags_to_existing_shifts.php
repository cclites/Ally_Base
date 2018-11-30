<?php

use App\Shift;

require __DIR__ . "/bootstrap.php";

////////////////////////////////////
//// This code is meant to be run with the release around November 29th, 2018
///  Author: Devon
////////////////////////////////////

$total = Shift::count();
$i = 0;
Shift::chunk(1000, function($shifts) use (&$i, $total) {
    $shifts->each(function(Shift $shift) {
        $shift->syncFlags(app(\App\Shifts\ShiftFlagManager::class)->generateFlags($shift));
    });
    $i++;
    $pct = round($i * 1000 / $total, 4) * 100;
    echo "${pct}% complete\n";
});
