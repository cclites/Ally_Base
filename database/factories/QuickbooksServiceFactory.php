<?php

use Faker\Generator as Faker;

$factory->define(App\QuickbooksService::class, function (Faker $faker) {
    return [
        'business_id' => function() {
            $business = \App\Business::inRandomOrder()->first() ?? factory(\App\Business::class)->create();
            return $business->id;
        },
        'service_id' => $faker->randomNumber(4),
        'name' => $faker->jobTitle(),
    ];
});
