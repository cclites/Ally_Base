<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientInvoice::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'payer_id' => function() {
            $payer = \App\Billing\Payer::inRandomOrder()->first() ?? factory(\App\Billing\Payer::class)->create();
            return $payer->id;
        },
        'name' => mt_rand(),
    ];
});
