<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientRate::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'service_id' => function() {
            if (mt_rand(0,1) === 0) return null; // 50% chance of null
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'payer_id' => function() {
            if (mt_rand(0,2) === 0) return null; // 33% chance of null
            $payer = \App\Billing\Payer::inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create();
            return $payer->id;
        },
        'caregiver_id' => function() {
            if (mt_rand(0,3) === 0) return null; // 25% chance of null
            $caregiver = \App\Caregiver::inRandomOrder()->first() ?? factory(\App\Caregiver::class)->create();
            return $caregiver->id;
        },
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'caregiver_hourly_rate' => $hourly = $faker->randomFloat(2, 15, 20),
        'caregiver_fixed_rate' => $faker->randomFloat(2, 50, 75),
        'client_hourly_rate' => $hourly = $faker->randomFloat(2, 20, 25),
        'client_fixed_rate' => $faker->randomFloat(2, 75, 100),
    ];
});
