<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\Payer::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'npi_number' => $faker->bankAccountNumber,
        'chain_id' => function() {
            $chain = \App\BusinessChain::inRandomOrder()->first() ?? factory(\App\BusinessChain::class)->create();
            return $chain->id;
        },
        'address1' => $faker->streetAddress,
        'address2' => $faker->randomElement([null, 'Apt' . mt_rand(1,2000), 'Suite #' . mt_rand(100,200)]),
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'zip' => $faker->randomNumber(5),
        'phone_number' => $faker->phoneNumber,
        'fax_number' => $faker->phoneNumber,
        'week_start' => $faker->numberBetween(0, 6),
    ];
});
