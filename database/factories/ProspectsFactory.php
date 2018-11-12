<?php

use Faker\Generator as Faker;
use App\Prospect;

$factory->define(Prospect::class, function (Faker $faker) {
    return [
        'business_id' => 1,
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'referral_source_id' => 1,
    ];
});