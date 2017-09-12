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

$factory->define(\App\Payment::class, function(Faker $faker) {
    return [
        'amount' => $faker->randomFloat(2, 0, 500),
        'transaction_id' => $faker->randomAscii,
        'transaction_code' => mt_rand(0,5),
        'success' => $faker->randomElement([0,1,1]),
    ];
});