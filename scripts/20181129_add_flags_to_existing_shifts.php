<?php

use App\Shift;

require __DIR__ . "/bootstrap.php";

////////////////////////////////////
//// This code is meant to be run with the release around November 29th, 2018
///  Author: Devon
////////////////////////////////////

$query = Shift::whereNotNull('checked_out_time');

$total = (clone $query)->count();
$i = 0;
(clone $query)->chunk(1000, function($shifts) use (&$i, $total) {
    DB::beginTransaction();
    $shifts->each(function(Shift $shift) {
//        $time1 = microtime(true);
        $shift->flagManager()->generate();
//        $time2 = microtime(true);
//        printf("Shift ID: %d Time: %sms\n", $shift->id, round($time2 - $time1, 6) * 1000);
    });
    $i++;
    $pct = round($i * 1000 / $total, 4) * 100;
    DB::commit();
    echo "${pct}% complete\n";
});
