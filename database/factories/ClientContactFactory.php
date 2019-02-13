<?php

use Faker\Generator as Faker;
use App\ClientContact;

$factory->define(App\ClientContact::class, function (Faker $faker) {
    return [
        'client_id' => function() {
            $client = \App\Client::inRandomOrder()->first() ?? factory(\App\Client::class)->create();
            return $client->id;
        },
        'name' => $faker->name,
        'relationship' => $faker->randomElement([ClientContact::RELATION_FAMILY, ClientContact::RELATION_OTHER, ClientContact::RELATION_POA, ClientContact::RELATION_PHYSICIAN]),
        'relationship_custom' => null,
        'phone1' => $faker->phoneNumber,
        'phone2' => $faker->phoneNumber,
        'email' => $faker->email,
        'address' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'zip' => $faker->randomNumber(5),
        'is_emergency' => false,
        'emergency_priority' => null,
    ];
});

$factory->state(App\ClientContact::class, 'emergency', [
    'is_emergency' => true,
    'emergency_priority' => 1,
]);
