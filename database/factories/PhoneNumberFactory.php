<?php

use Faker\Generator as Faker;
use App\PhoneNumber;

$factory->define(PhoneNumber::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['primary', 'primary', 'billing']),
        'number' => $faker->phoneNumber
    ];
});
