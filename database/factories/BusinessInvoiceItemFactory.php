<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\BusinessInvoiceItem::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3),
        'units' => $units = mt_rand(1,4),
        'client_rate' => $clientRate = $faker->randomFloat(2, 20, 25),
        'caregiver_rate' => $cgRate = $faker->randomFloat(2, 10, 15),
        'ally_rate' => $allyRate = $clientRate * 0.05,
        'rate' => $rate = $clientRate - $cgRate - $allyRate,
        'total' => $units * $rate,
        'date' => $faker->dateTime,
        'notes' => $faker->randomElement([null, $faker->sentence]),
    ];
});
