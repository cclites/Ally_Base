<?php

use Faker\Generator as Faker;
use App\Business;

$factory->define(App\StatusAlias::class, function (Faker $faker) {
    if (! Business::exists()) {
        // Create a business if one does not exist.  Client's need to be attached to a business.
        factory(Business::class)->create();
    }

    return [
        'business_id' => Business::inRandomOrder()->value('id'),
        'name' => $faker->colorName(),
        'active' => $faker->randomElement([1, 0]),
        'type' => $faker->randomElement(['client', 'caregiver']),
    ];
});
