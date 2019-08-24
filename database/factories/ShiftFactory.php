<?php

use Faker\Generator as Faker;
use App\Shift;
use App\Client;
use App\Caregiver;

$factory->define(Shift::class, function(Faker $faker) {
    if (!Client::exists()) {
        // Create a client & caregiver if none exist.  Shifts need to be attached to clients & caregivers
        factory(Client::class)->create();
        factory(Caregiver::class)->create();
    }

    $duration = mt_rand(60, 720);
    $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));
    $latitude = $faker->randomFloat(4, 32, 46);
    $longitude = $faker->randomFloat(4, -118, -74);

    // Attach a random caregiver to a client if client does not have caregivers already
    $client = Client::inRandomOrder()->first();
    if ( $client && !$client->caregivers->count() ) {

        $caregiver = $client->business->caregivers()->inRandomOrder()->first() ?? null;

        if ( $caregiver ) {

            $client->caregivers()->attach( $caregiver, [

                'caregiver_hourly_rate' => $faker->randomFloat(2, 10, 25),
                'provider_hourly_fee' => $faker->randomFloat(2, 5, 10),
            ]);
            $client->save();
            $client->refresh();
        }
    }

    return [
        'client_id' => ($client) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'caregiver_id' => $client->caregivers->shuffle()->first()->id ?? null,
        'checked_in_time' => $start,
        'checked_in_method' => Shift::METHOD_GEOLOCATION,
        'checked_in_latitude' => $latitude,
        'checked_in_longitude' => $longitude,
        'checked_in_number' => null,
        'checked_out_time' => $end,
        'checked_out_method' => ($end) ? Shift::METHOD_GEOLOCATION : null,
        'checked_out_latitude' => ($end) ? $latitude : null,
        'checked_out_longitude' => ($end) ? $longitude : null,
        'checked_out_number' => null,
        'status' => $faker->randomElement(['WAITING_FOR_AUTHORIZATION', 'WAITING_FOR_CHARGE', 'WAITING_FOR_PAYOUT', 'PAID', 'PAID']),
        'caregiver_comments' => $faker->sentence
    ];
});