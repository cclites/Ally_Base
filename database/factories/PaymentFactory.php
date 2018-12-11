<?php

use Faker\Generator as Faker;
use App\Payment;
use App\Client;

$factory->define(Payment::class, function(Faker $faker) {
    return [
        'client_id' => ($client = Client::inRandomOrder()->first()) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'amount' => $faker->randomFloat(2, 0, 500),
        'transaction_id' => $faker->randomAscii,
        'transaction_code' => mt_rand(0,5),
    ];
});