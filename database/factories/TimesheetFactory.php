<?php

use Faker\Generator as Faker;

$factory->define(App\Timesheet::class, function (Faker $faker) {
    if (!\App\Client::exists()) {
        // Create a client & caregiver if none exist.  Shifts need to be attached to clients & caregivers
        factory(\App\Client::class)->create();
        factory(\App\Caregiver::class)->create();
    }

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

    $caregiver = $client->caregivers->shuffle()->first();

    return [
        'client_id' => ($client) ? $client->id : null,
        'business_id' => $client->business_id ?? null,
        'caregiver_id' => $caregiver->id,
        'creator_id' => $caregiver->id,
    ];
});

$factory->define(App\TimesheetEntry::class, function (Faker $faker) {
    $duration = mt_rand(60, 720);
    $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));

    return [
        // 'timesheet_id' => factory('App\Timesheet')->create()->id,
        'checked_in_time' => $start,
        'checked_out_time' => $end,
        'mileage' => $faker->numberBetween(0, 50),
        'other_expenses' => $faker->numberBetween(0, 15),
        'caregiver_comments' => $faker->sentence($faker->numberBetween(0, 5)),
        'caregiver_rate' => $faker->numberBetween(8, 20),
        'provider_fee' => $faker->numberBetween(1, 3),
    ];
});
