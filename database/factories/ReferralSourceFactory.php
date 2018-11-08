<?php

use Faker\Generator as Faker;
use App\ReferralSource;

$factory->define(ReferralSource::class, function (Faker $faker) {
    return [
        'business_id' => 1,
        'organization' => $faker->company,
        'contact_name' => $faker->name,
        'phone' => $faker->tollFreePhoneNumber,
    ];
});
