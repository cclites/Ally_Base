<?php

use Faker\Generator as Faker;

$factory->define(App\SmsThreadReply::class, function (Faker $faker) {
    return [
        'from_number' => '1234567890',
        'to_number' => '1234567890',
        'message' => $faker->sentence(),
        'twilio_message_id' => md5($faker->uuid()),
        'read_at' => null,
    ];
});
