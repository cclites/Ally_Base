<?php

use Faker\Generator as Faker;
use App\ReferralSource;

$factory->define(ReferralSource::class, function (Faker $faker) {
    if (! Business::exists()) {
        // Create a business if one does not exist.  ReferralSources
        // need to be attached to a business chain.
        factory(Business::class)->create();
    }

    return [
        'chain_id' => Business::inRandomOrder()->first()->chain_id,
        'organization' => $faker->company,
        'contact_name' => $faker->name,
        'phone' => $faker->tollFreePhoneNumber,
    ];
});
