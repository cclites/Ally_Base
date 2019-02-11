<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\GatewayTransaction::class, function (Faker $faker) {
    return [
        'gateway_id' => 'factory',
        'transaction_id' => mt_rand(10000000,99999999),
        'transaction_type' => 'sale',
        'amount' => $faker->randomFloat(2, 0, 1000),
        'success' => true,
        'declined' => false,
    ];
});
