<?php
/**
 * This file can be used to test individual PHP components or CLI commands
 */

////////////////////////////////////
//// Bootstrap Laravel and Vendors
////////////////////////////////////
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

////////////////////////////////////
//// Test code below here
////////////////////////////////////

$client = \App\Client::first();
$caregiver = $client->caregivers->where('id', 88)->first();
$rates = app(\App\Shifts\RateFactory::class)->getRatesForClientCaregiver($client, $caregiver, true);

var_dump($rates);