<?php

use Faker\Generator as Faker;

$factory->define(App\CarePlan::class, function (Faker $faker) {
    return [
        'business_id' => function () {
            return factory('App\Business')->create()->id;
        },
        'client_id' => function () {
            return factory('App\Client')->create()->id;
        },
        'notes' => $faker->paragraph,
        'name' => $faker->sentence(3),
    ];
});
