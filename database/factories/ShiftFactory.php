<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Shift::class, function(Faker $faker) {
    if (!\App\Client::exists()) {
        // Create a client & caregiver if none exist.  Shifts need to be attached to clients & caregivers
        factory(\App\Client::class)->create();
        factory(\App\Caregiver::class)->create();
    }

    $duration = mt_rand(60, 720);
    $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));
    $latitude = $faker->randomFloat(4, 32, 46);
    $longitude = $faker->randomFloat(4, -118, -74);

    // Attach a random caregiver to a client if client does not have caregivers already
    $client = App\Client::inRandomOrder()->first();
    if ($client && !$client->caregivers->count()) {
        $caregiver = $client->business->caregivers()->inRandomOrder()->first() ?? null;
        if ($caregiver) {
            $client->caregivers()->attach($caregiver, [
                'caregiver_hourly_rate' => $faker->randomFloat(2, 10, 25),
                'provider_hourly_fee' => $faker->randomFloat(2, 5, 10),
            ]);
        }
    }

    return [
        'client_id' => ($client) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'caregiver_id' => $client->caregivers->shuffle()->first()->id ?? null,
        'checked_in_time' => $start,
        'checked_in_method' => \App\Shift::METHOD_GEOLOCATION,
        'checked_in_latitude' => $latitude,
        'checked_in_longitude' => $longitude,
        'checked_in_number' => null,
        'checked_out_time' => $end,
        'checked_out_method' => ($end) ? \App\Shift::METHOD_GEOLOCATION : null,
        'checked_out_latitude' => ($end) ? $latitude : null,
        'checked_out_longitude' => ($end) ? $longitude : null,
        'checked_out_number' => null,
        'status' => $faker->randomElement(['WAITING_FOR_AUTHORIZATION', 'WAITING_FOR_CHARGE', 'WAITING_FOR_PAYOUT', 'PAID', 'PAID']),
        'caregiver_comments' => $faker->sentence
    ];
});

$factory->define(App\ShiftIssue::class, function(Faker $faker) {
    return [
        'shift_id' => function () {
            return factory('App\Shift')->create()->id;
        },
        'client_injury' => $faker->randomElement([0,0,1]),
        'caregiver_injury' => $faker->randomElement([0,0,1]),
        'comments' => (mt_rand(0,1) === 0) ? $faker->paragraph : null,
    ];
});

$factory->define(App\ShiftActivity::class, function(Faker $faker) {
    return [
        'shift_id' => function () {
            return factory('App\Shift')->create()->id;
        },
        'activity_id' => function () {
            return factory('App\Activity')->create()->id;
        },
        'completed' => $faker->boolean
    ];
});