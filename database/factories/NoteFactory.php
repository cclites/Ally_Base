<?php

use Faker\Generator as Faker;

$factory->define(\App\Note::class, function (Faker $faker) {
    return [
        'business_id' => function () {
            return factory('App\Business')->create()->id;
        },
        'caregiver_id' => function() {
            return factory('App\Caregiver')->create()->id;
        },
        'client_id' => function () {
            return factory('App\Client')->create()->id;
        },
        'body' => $faker->paragraph,
        'tags' => $faker->word,
        'created_by' => function() {
            return factory('App\OfficeUser')->create()->id;
        }
    ];
});
