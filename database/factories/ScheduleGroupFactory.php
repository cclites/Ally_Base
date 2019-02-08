<?php

use Faker\Generator as Faker;

$factory->define(\App\ScheduleGroup::class, function (Faker $faker) {
    return [
        'rrule' => 'INTERVAL=1;FREQ=WEEKLY',
        'starts_at' => $faker->dateTime,
        'end_date' => $faker->date('Y-m-d', '+2 years'),
        'interval_type' => 'weekly',
    ];
});
