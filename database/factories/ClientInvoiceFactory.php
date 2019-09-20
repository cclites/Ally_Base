<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientInvoice::class, function (Faker $faker) {
    return [
        'client_id' => function () {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'client_payer_id' => function () {
            $payer = \App\Billing\ClientPayer::inRandomOrder()->first() ?? factory(\App\Billing\ClientPayer::class)->create();
            return $payer->id;
        },
        'name' => mt_rand(),
    ];
});
