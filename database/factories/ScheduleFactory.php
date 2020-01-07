<?php

use Faker\Generator as Faker;
use App\Schedule;
use App\Client;
use Carbon\Carbon;

$factory->define(Schedule::class, function(Faker $faker) {
    $datetime = $faker->date('Y-m-d', Carbon::now()->addMonths(3)) . ' '
        . str_pad(mt_rand(1,23), 2, '0', STR_PAD_LEFT) . $faker->randomElement(['00', '15', '30']);
    $starts_at = new Carbon($datetime);

    // Create a client if one doesn't exist
    $client = Client::inRandomOrder()->first();
    if (!$client) {
        $client = factory(Client::class)->create();
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

    $caregiverRate = $faker->randomFloat(2, 10, 25);
    $providerRate = $faker->randomFloat(2, 5, 10);
    // TODO: Use an actual rate calculator to get the proper ally fee
    $clientRate = add(
        add($caregiverRate, $providerRate),
        multiply(add($caregiverRate, $providerRate), 0.06),
    );

    return [
        'client_id' => $client->id,
        'business_id' => $client->business_id,
        'caregiver_id' => $client->caregivers->shuffle()->first()->id ?? null,
        'starts_at' => $starts_at,
        'weekday' => $starts_at->format('w'),
        'duration' => $faker->randomElement([60, 60, 90, 120, 120, 150, 180, 240, 300, 480, 480, 720, 1440]),
        'caregiver_rate' => $caregiverRate,
        'provider_fee' => $providerRate,
        'client_rate' => $clientRate,
        'fixed_rates' => 0,
        'hours_type' => 'default',
        'status' => Schedule::OK,
    ];
});
