<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Business::class, function(Faker $faker) {
    return [
        'name' => $faker->unique()->company,
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
            $chain = \App\BusinessChain::inRandomOrder()->first();
            if (!$chain) $chain = factory(\App\BusinessChain::class)->create();
            return $chain->id;
        }
    ];
});

$factory->define(\App\BusinessChain::class, function(Faker $faker) {
    $name = $faker->unique()->company;
    return [
        'name' => $name,
        'slug' => str_slug($name),
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5),
        'phone1' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
    ];
});
