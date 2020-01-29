<?php

use Faker\Generator as Faker;
use App\BusinessChain;
use Illuminate\Support\Str;

$factory->define(BusinessChain::class, function(Faker $faker) {
    $companyName = $faker->unique()->company;
    return [
        'name' => $companyName,
        'slug' => Str::slug($companyName),
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
