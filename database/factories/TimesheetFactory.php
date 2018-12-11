<?php

use Faker\Generator as Faker;
use App\Timesheet;
use App\Client;
use App\Caregiver;

$factory->define(Timesheet::class, function (Faker $faker) {
    if (!Client::exists()) {
        // Create a client & caregiver if none exist.  Shifts need to be attached to clients & caregivers
        factory(Client::class)->create();
        factory(Caregiver::class)->create();
    }

    // Attach a random caregiver to a client if client does not have caregivers already
    $client = Client::inRandomOrder()->first();
    if ($client && !$client->caregivers->count()) {
        $caregiver = $client->business->caregivers()->inRandomOrder()->first() ?? null;
        if ($caregiver) {
            $client->caregivers()->attach($caregiver, [
                'caregiver_hourly_rate' => $faker->randomFloat(2, 10, 25),
                'provider_hourly_fee' => $faker->randomFloat(2, 5, 10),
            ]);
        }
    }

    $caregiver = $client->caregivers->shuffle()->first();

    return [
        'client_id' => ($client) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'caregiver_id' => $caregiver->id,
        'creator_id' => $caregiver->id,
    ];
});

