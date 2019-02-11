<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\BusinessInvoice::class, function (Faker $faker) {
    return [
        'business_id' => function() {
            $business = \App\Business::inRandomOrder()->first() ?? factory(\App\Business::class)->create();
            return $business->id;
        },
        'name' => mt_rand(),
    ];
});
