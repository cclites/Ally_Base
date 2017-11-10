<?php

use Faker\Generator as Faker;

$factory->define(App\Activity::class, function (Faker $faker) {
    return [
        'business_id' => function() {
            return factory('App\Business')->create()->id;
        },
        'code' => str_random(4),
        'name' => $faker->word,
        'description' => $faker->sentence
    ];
});
