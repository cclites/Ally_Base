<?php

use Faker\Generator as Faker;
use App\Prospect;
use App\Business;

$factory->define(Prospect::class, function(Faker $faker) {
    if (!Business::exists()) {
        // Create a business if one does not exist.  Client's need to be attached to a business.
        factory(Business::class)->create();
    }

    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->email,
        'date_of_birth' => $faker->date('Y-m-d', '-20 years'),
        'client_type' => 'required',
        'phone' => $faker->phoneNumber,
        'address1' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => 'FL',
        'zip' => mt_rand(40000,60000),
        'country' => 'US',
        'last_contacted' => $faker->date(),
        'initial_call_date' => $faker->date(),
        'had_initial_call' => mt_rand(0,1),
        'had_assessment_scheduled' => mt_rand(0,1),
        'had_assessment_performed' => mt_rand(0,1),
        'needs_contract' => mt_rand(0,1),
        'expecting_client_signature' => mt_rand(0,1),
        'needs_payment_info' => mt_rand(0,1),
        'ready_to_schedule' => mt_rand(0,1),
        'closed_loss' => mt_rand(0,1),
        'closed_win' => mt_rand(0,1),
        'business_id' => Business::inRandomOrder()->value('id'),
    ];
});
