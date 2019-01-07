<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientPayer::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'payer_id' => function() {
            $payer = \App\Billing\Payer::inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create();
            return $payer->id;
        },
        'policy_number' => $faker->bankAccountNumber,
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'payment_allocation' => 'balance',
    ];
});
