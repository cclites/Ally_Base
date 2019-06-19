<?php

use Faker\Generator as Faker;
use App\BusinessCommunications;

$factory->define(BusinessCommunications::class, function (Faker $faker) {
    return [
        'auto_off'=>false,
        'on_indefinitely'=>false,
        'week_start'=> $faker->randomElement(['13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00']),
        'week_end'=>$faker->randomElement(['03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00']),
        'weekend_start'=>$faker->randomElement(['13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00']),
        'weekend_end'=>$faker->randomElement(['03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00']),
        'message'=>$faker->sentence,
        'business_id' => function() {
            $business = \App\Business::inRandomOrder()->first() ?? factory(\App\Business::class)->create();
            return $business->id;
        },
    ];
});
