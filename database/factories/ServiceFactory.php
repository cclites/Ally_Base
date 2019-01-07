<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\Service::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2),
        'default' => false,
        'chain_id' => function() {
            $chain = \App\BusinessChain::inRandomOrder()->first() ?? factory(\App\BusinessChain::class)->create();
            return $chain->id;
        }
    ];
});
