<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Activity::class, function(Faker $faker) {
    return [
        'code' => str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT),
        'name' => $faker->word,
        'description' => $faker->sentence,
    ];
});

$factory->define(\App\Business::class, function(Faker $faker) {
    return [
        'name' => $faker->company,
        'type' => 'Registry',
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5),
        'phone1' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
        'default_commission_rate' => mt_rand(500, 9000) / 100,
    ];
});
