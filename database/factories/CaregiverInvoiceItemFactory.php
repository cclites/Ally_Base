<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\CaregiverInvoiceItem::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(3),
        'units' => $units = mt_rand(1,4),
        'rate' => $rate = $faker->randomFloat(2, 10, 15),
        'total' => $units * $rate,
        'date' => $faker->dateTime,
        'notes' => $faker->randomElement([null, $faker->sentence]),
    ];
});
