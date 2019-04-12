<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ScheduleService::class, function (Faker $faker) {
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
        'client_rate' => $faker->randomFloat(2, 20, 25),
        'caregiver_rate' => $faker->randomFloat(2, 15, 20),
    ];
});
