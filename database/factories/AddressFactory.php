<?php

use Faker\Generator as Faker;
use App\Address;

$factory->define(Address::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['billing', 'evv', 'home']),
        'address1' => $faker->streetAddress,
        'address2' => $faker->randomElement([null, 'Apt' . mt_rand(1,2000), 'Suite #' . mt_rand(100,200)]),
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5)
    ];
});
