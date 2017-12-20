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
    $start = $faker->date();
    $end = date('Y-m-d', strtotime($start . ' +' . (string) mt_rand(10, 1000) . ' days'));
    $rruleFreq = $faker->randomElement(['WEEKLY', 'WEEKLY', 'MONTHLY']);
    $rruleByday = $faker->randomElement(['MO', 'TU', 'WE', 'TH', 'FR', 'MO,WE', 'TU,TH', 'WE,FR', 'MO,TU', 'MO,TU,WE']);
    $rruleInterval = $faker->randomElement([1,1,1,2]);

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
        'start_date' => $start,
        'end_date' => $end,
        'time' => $faker->randomElement(['08', '09', '11', '14', '15']) . ':' . $faker->randomElement(['00', '15', '30']) . ':00',
        'duration' => $faker->randomElement([90, 120, 180, 240]),
        'rrule' => sprintf('FREQ=%s;BYDAY=%s;INTERVAL=%s', $rruleFreq, $rruleByday, $rruleInterval),
        'notes' => $faker->randomElement([$faker->sentence, $faker->paragraph, null]),
        'caregiver_rate' => $faker->randomFloat(2, 10, 25),
        'provider_fee' => $faker->randomFloat(2, 5, 10),
    ];
});