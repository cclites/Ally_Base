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

$factory->define(\App\Schedule::class, function(Faker $faker) {
    $datetime = $faker->date('Y-m-d', \Carbon\Carbon::now()->addMonths(3)) . ' '
        . str_pad(mt_rand(1,23), 2, '0', STR_PAD_LEFT) . $faker->randomElement(['00', '15', '30']);
    $starts_at = new Carbon\Carbon($datetime);

    // Create a client if one doesn't exist
    $client = App\Client::inRandomOrder()->first();
    if (!$client) {
        $client = factory(\App\Client::class)->create();
    }

    // Attach a random caregiver to a client if client does not have caregivers already
    if (!$client->caregivers->count()) {
        $caregiver = $client->business->caregivers()->inRandomOrder()->first() ?? null;
        if ($caregiver) {
            $client->caregivers()->attach($caregiver, [
                'caregiver_hourly_rate' => $faker->randomFloat(2, 10, 25),
                'provider_hourly_fee' => $faker->randomFloat(2, 5, 10),
            ]);
        }
    }

    return [
        'client_id' => $client->id,
        'business_id' => $client->business_id,
        'caregiver_id' => $client->caregivers->shuffle()->first()->id ?? null,
        'starts_at' => $starts_at,
        'weekday' => $starts_at->format('w'),
        'duration' => $faker->randomElement([60, 60, 90, 120, 120, 150, 180, 240, 300, 480, 480, 720, 1440]),
        'caregiver_rate' => $faker->randomFloat(2, 10, 25),
        'provider_fee' => $faker->randomFloat(2, 5, 10),
        'fixed_rates' => 0,
    ];
});
