<?php

use Faker\Generator as Faker;
use App\Business;
use App\BusinessChain;

$factory->define(Business::class, function(Faker $faker) {
    return [
        'name' => $name = $faker->unique()->company,
        'short_name' => $name,
        'type' => 'Registry',
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5),
        'phone1' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
        'default_commission_rate' => mt_rand(500, 9000) / 100,
        'timezone' => $faker->randomElement(['America/Los_Angeles', 'America/Phoenix', 'UTC', 'America/New_York']),
        'contact_name' => $faker->name,
        'contact_email' => $faker->safeEmail,
        'contact_phone' => $faker->phoneNumber,
        'chain_id' => function() {
            $chain = BusinessChain::inRandomOrder()->first();
            if (!$chain) $chain = factory(BusinessChain::class)->create();
            return $chain->id;
        },
        'require_caregiver_signatures' => false,
    ];
});
