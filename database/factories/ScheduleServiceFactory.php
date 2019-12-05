<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ScheduleService::class, function (Faker $faker) {
    // Always make sure client rate is 25-50% more than the caregiver rate,
    // ensuring that there is room for ally fee and a POSITIVE registry fee.
    $cgRate = $faker->randomFloat(2, 20, 25);
    $clientRate = $cgRate + round($cgRate * $faker->randomFloat(2, 0.25, 0.5), 2);

    return [
        'schedule_id' => function() {
            $schedule = \App\Schedule::inRandomOrder()->first() ?? factory(\App\Schedule::class)->create();
            return $schedule->id;
        },
        'service_id' => function() {
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'payer_id' => null,
        'hours_type' => 'default',
        'duration' => mt_rand(1,5),
        'client_rate' => $clientRate,
        'caregiver_rate' => $cgRate,
    ];
});
