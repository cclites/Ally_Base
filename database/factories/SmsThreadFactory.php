<?php

use Faker\Generator as Faker;

$factory->define(App\SmsThread::class, function (Faker $faker) {
    return [
        'from_number' => '1234567890',
        'message' => $faker->sentence(),
        'can_reply' => 1,
        'sent_at' => $faker->dateTimeThisMonth(),
    ];
});
