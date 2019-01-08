<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\PayerRate::class, function (Faker $faker) {
    return [
        'service_id' => function() {
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'payer_id' => function() {
            $payer = \App\Billing\Payer::inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create();
            return $payer->id;
        },
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'hourly_rate' => $hourly = $faker->randomFloat(2, 15, 25),
        'fixed_rate' => $faker->randomFloat(2, 50, 100),
    ];
});
