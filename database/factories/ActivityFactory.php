<?php

use Faker\Generator as Faker;

$factory->define(App\Activity::class, function (Faker $faker) {
    return [
        'business_id' => null,
        'code' => '0' . $faker->unique()->randomNumber(2),
        'name' => $faker->word,
        'description' => $faker->sentence
    ];
});
