<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\Payer::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'npi_number' => $faker->bankAccountNumber,
        'chain_id' => function() {
            $chain = \App\BusinessChain::inRandomOrder()->first() ?? factory(\App\BusinessChain::class)->create();
            return $chain->id;
        }
    ];
});
