<?php

use App\Billing\ClientInvoice;
use App\Shift;
use Faker\Generator as Faker;

$factory->define(\App\Billing\ClientInvoiceItem::class, function (Faker $faker) {
    return [

        'name' => $faker->sentence(3),
        'units' => $units = mt_rand(1,4),
        'rate' => $rate = $faker->randomFloat(2, 10, 20),
        'total' => $units * $rate,
        'amount_due' => $units * $rate * $faker->randomFloat(1, 0, 1),
        'date' => $faker->dateTime,
        'notes' => $faker->randomElement([null, $faker->sentence]),
        'invoice_id'       => factory( ClientInvoice::class ),
        'invoiceable_type' => 'shifts',
        'invoiceable_id'   => factory( Shift::class ),
    ];
});
