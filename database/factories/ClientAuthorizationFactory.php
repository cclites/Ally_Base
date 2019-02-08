<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientAuthorization::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'service_id' => function() {
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'payer_id' => function() {
            if (mt_rand(0,3) === 0) return null; // 25% null
            $payer = \App\Billing\Payer::inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create();
            return $payer->id;
        },
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'units' => ($units = mt_rand(5,10)) === 5 ? 5.25 : $units, // small chance of a decimal
        'unit_type' => 'hourly',
        'period' => $faker->randomElement(['daily', 'weekly', 'monthly']),
        'notes' => $faker->randomElement([$faker->sentence, null]),
    ];
});
