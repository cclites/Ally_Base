<?php

use Faker\Generator as Faker;
use App\Billing\ClientAuthorization;

$factory->define(\App\Billing\ClientAuthorization::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'service_id' => function() {
            $service = \App\Billing\Service::inRandomOrder()->first() ?? factory(\App\Billing\Service::class)->create();
            return $service->id;
        },
        'effective_start' => $faker->date('Y-m-d', 'now'),
        'effective_end' => '9999-12-31',
        'units' => ($units = mt_rand(5,10)) === 5 ? 5.25 : $units, // small chance of a decimal
        'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
        'period' => $faker->randomElement([ClientAuthorization::PERIOD_DAILY, ClientAuthorization::PERIOD_MONTHLY, ClientAuthorization::PERIOD_WEEKLY]),
        'notes' => $faker->randomElement([$faker->sentence, null]),
    ];
});
