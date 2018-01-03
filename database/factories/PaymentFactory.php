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
        'client_id' => ($client = App\Client::inRandomOrder()->first()) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'amount' => $faker->randomFloat(2, 0, 500),
        'transaction_id' => $faker->randomAscii,
        'transaction_code' => mt_rand(0,5),
    ];
});

$factory->define(\App\Deposit::class, function(Faker $faker) {
    return [
        'deposit_type' => $faker->randomElement(['caregiver', 'business']),
        'amount' => $faker->randomFloat(2, 0, 500),
        'transaction_id' => $faker->randomAscii,
        'transaction_code' => mt_rand(0,5),
    ];
});