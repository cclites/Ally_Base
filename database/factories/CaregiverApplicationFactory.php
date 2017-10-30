<?php

use Faker\Generator as Faker;

$factory->define(\App\CaregiverApplication::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'middle_initial' => $faker->randomLetter,
        'date_of_birth' => $faker->date(),
        'ssn' => $faker->randomNumber(400000000, 700000000),
        'address' => $faker->streetAddress,
        'address_2' => $faker->randomNumber(),
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'zip' => $faker->postcode,
        'cell_phone' => $faker->phoneNumber,
        'cell_phone_provider' => $faker->word,
        'home_phone' => $faker->phoneNumber,
        'email' => $faker->safeEmail,
        'emergency_contact_name' => $faker->name,
        'emergency_contact_phone' => $faker->phoneNumber,
        'worked_here_before' => $faker->boolean,
        'worked_before_location' => '',
        'caregiver_position_id' => function () {
            return factory('App\CaregiverPosition')->create()->id;
        },
        'preferred_start_date' => $faker->date(),
        'preferred_days' => 'mon,tues,wed,thurs,fri',
        'preferred_times' => 'mornings,afternoons,evenings,nights',
        'preferred_shift_length' => array_random([1, 4, 8, 12]),
        'work_weekends' => $faker->boolean,
        'travel_radius' => array_random([5, 10, 15, 20]),
        'dui' => $faker->boolean,
        'reckless_driving' => $faker->boolean,
        'moving_violation' => $faker->boolean,
        'moving_violation_count' => null,
        'accidents' => $faker->boolean,
        'accident_count' => null
    ];
});

$factory->define(\App\CaregiverPosition::class, function (Faker $faker) {
    return [
        'name' => $faker->word
    ];
});