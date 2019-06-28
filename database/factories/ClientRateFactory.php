<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientRate::class, function (Faker $faker) {
    $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();

    return [
        'client_id' => function() use ($client) {
            return $client->id;
        },
        'service_id' => function() use ($client) {
            if (mt_rand(0,1) === 0) return null; // 50% chance of null
            $service = $client->business->chain->services()->inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create(['chain_id' => $client->business->chain_id]);
            return $service->id;
        },
        'payer_id' => function() use ($client) {
            if (mt_rand(0,2) === 0) return null; // 33% chance of null
            $payer = $client->business->chain->payers()->inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create(['chain_id' => $client->business->chain_id]);
            return $payer->id;
        },
        'caregiver_id' => function() {
            $caregiver = \App\Caregiver::inRandomOrder()->first() ?? factory(\App\Caregiver::class)->create();
            return $caregiver->id;
        },
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'caregiver_hourly_rate' => $hourly = $faker->randomFloat(2, 15, 20),
        'caregiver_fixed_rate' => $faker->randomFloat(2, 50, 75),
        'client_hourly_rate' => $hourly = $faker->randomFloat(2, 25, 30),
        'client_fixed_rate' => $faker->randomFloat(2, 100, 125),
    ];
});
