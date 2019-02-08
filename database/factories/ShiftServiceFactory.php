<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\Invoiceable\ShiftService::class, function (Faker $faker) {
    return [
        'shift_id' => function() {
            $shift = \App\Shift::inRandomOrder()->first() ?? factory(\App\Shift::class)->create();
            return $shift->id;
        },
        'service_id' => function() {
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'client_payer_id' => null,
        'hours_type' => 'default',
        'duration' => mt_rand(1,5),
        'client_rate' => $faker->randomFloat(2, 20, 25),
        'caregiver_rate' => $faker->randomFloat(2, 15, 20),
    ];
});
