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

$companies = [];
$factory->define(\App\Business::class, function(Faker $faker) use ($companies) {
    // Ensure a unique company name when creating multiple
    $company = $faker->company;
    while(in_array($company, $companies)) {
        $company = $faker->company;
    }
    $companies[] = $company;
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
    ];
});
