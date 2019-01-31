<?php

require __DIR__ . '/bootstrap.php';

$lancId = 47;
$yorkId = 62;
$clients = \App\Client::where('business_id', $lancId)->where('client_type_descriptor', 'LIKE', 'York/%')->get();

DB::beginTransaction();
foreach($clients as $client) {
    $client->update(['business_id' => $yorkId]);
    $client->payments()->update(['business_id' => $yorkId]);
    $client->shifts()->update(['business_id' => $yorkId]);
    $client->schedules()->update(['business_id' => $yorkId]);
}
DB::commit();