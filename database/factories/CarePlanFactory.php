<?php

use Faker\Generator as Faker;
use App\CarePlan;
use App\Business;
use App\Client;

$factory->define(CarePlan::class, function (Faker $faker) {
    return [
        'business_id' => function () {
            return factory(Business::class)->create()->id;
        },
        'client_id' => function () {
            return factory(Client::class)->create()->id;
        },
        'notes' => $faker->paragraph,
        'name' => $faker->sentence(3),
    ];
});
