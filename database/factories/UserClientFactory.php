<?php

use Faker\Generator as Faker;
use App\Client;
use App\Business;

require_once 'UserFactory.php';

$factory->define(Client::class, function(Faker $faker) {
    if (!Business::exists()) {
        // Create a business if one does not exist.  Client's need to be attached to a business.
        factory(Business::class)->create();
    }

    return array_merge(userFactory($faker), [
        'ssn' => $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4),
        'business_id' => Business::inRandomOrder()->value('id'),
        'client_type' => $faker->randomElement(['private_pay', 'medicaid', 'LTCI']),
        'agreement_status' => $faker->randomElement([null, Client::NEEDS_AGREEMENT, Client::SIGNED_ELECTRONICALLY]),
        'hospital_name' => $faker->company,
        'hospital_number' => $faker->phoneNumber,
        'inquiry_date' => null,
        'service_start_date' => null,
        'send_1099' => 'yes',
        'can_edit_send_1099' => false,
    ]);
});
