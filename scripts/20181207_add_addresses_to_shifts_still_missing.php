<?php

use App\Shift;

require __DIR__ . "/bootstrap.php";

////////////////////////////////////
//// This code is meant to be run with the release around December 7th, 2018
///  Author: Devon
////////////////////////////////////

$shifts = Shift::select('id', 'client_id', 'address_id')
    ->with(['client', 'client.evvAddress'])
    ->whereNotNull('checked_in_latitude')
    ->whereNull('address_id')
    ->whereHas('client', function($q) {
        $q->has('evvAddress');
    })
    ->get();

foreach($shifts as $shift) {
    $shift->update(['address_id' => $shift->client->evvAddress->id]);
}