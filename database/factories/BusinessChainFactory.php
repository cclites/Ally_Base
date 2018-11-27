<?php

use Faker\Generator as Faker;
use App\BusinessChain;

$factory->define(BusinessChain::class, function(Faker $faker) {
    return [
        'name' => $faker->unique()->company,
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5),
        'phone1' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
    ];
});
