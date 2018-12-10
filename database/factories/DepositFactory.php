<?php

use Faker\Generator as Faker;
use App\Deposit;

$factory->define(Deposit::class, function(Faker $faker) {
    return [
        'deposit_type' => $faker->randomElement(['caregiver', 'business']),
        'amount' => $faker->randomFloat(2, 0, 500),
        'transaction_id' => $faker->randomAscii,
        'transaction_code' => mt_rand(0,5),
    ];
});
