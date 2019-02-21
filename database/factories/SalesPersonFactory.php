<?php

use Faker\Generator as Faker;

$factory->define(\App\SalesPerson::class, function (Faker $faker) {
    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->safeEmail,
        'active' => true,
        'business_id' => function () {
            return factory(\App\Business::class)->create()->id;
        }
    ];
});
