<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\Invoiceable\ShiftAdjustment::class, function (Faker $faker) {
    $client = null;

    return [
        'client_id' => function() use ($client) {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'business_id' => function() use ($client) {
            if ($client) return $client->business_id;
            $business = \App\Business::inRandomOrder()->first() ?? factory(\App\Business::class)->create();
            return $business->id;
        },
        'caregiver_id' => function() {
            $caregiver = \App\Caregiver::inRandomOrder()->first() ?? factory(\App\Caregiver::class)->create();
            return $caregiver->id;
        },
        'units' => mt_rand(1,5),
        'client_rate' => $faker->randomFloat(2, -20, 50),
        'caregiver_rate' => $faker->randomFloat(2, -20, 50),
        'ally_rate' => $faker->randomFloat(2, -2, 2),
        'status' => 'WAITING_FOR_INVOICE' // TODO change this
    ];
});
