<?php

use Faker\Generator as Faker;
use App\RateCode;
use App\Business;

$factory->define(RateCode::class, function(Faker $faker) {
    return [
        'name' => $faker->name,
        'business_id' => function() {
            return Business::inRandomOrder()->first()->id;
        },
        'type' => $faker->randomElement(['client', 'caregiver']),
        'rate' => $faker->randomFloat(2, 10, 100),
        'fixed' => $faker->randomElement([true, false]),
    ];
});