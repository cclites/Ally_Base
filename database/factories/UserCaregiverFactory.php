<?php

use Faker\Generator as Faker;
use App\Caregiver;
use App\Address;

require_once 'UserFactory.php';

$factory->define(Caregiver::class, function(Faker $faker) {
    $user_data = userFactory($faker);
    $ssn = $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4);
    return array_merge($user_data, [
        'uses_ein_number' => false,
        'ssn' => $ssn,
        'title' => $faker->randomElement(['CNA', 'LPN', 'RN']),
        'hire_date' => $faker->date(),
        'gender' => $faker->randomElement([null, null, 'M', 'F']),
    ]);
});

$factory->state(Caregiver::class, 'w9', function(Faker $faker) {
    $user_data = userFactory($faker);
    $ssn = $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4);
    $tax_classifications = collect([
        ['name' => 'Individual/sole proprietor or single-member LLC', 'value' => 'individual_sole_prop'],
        ['name' => 'C Corporation', 'value' => 'c_corp'],
        ['name' => 'S Corporation', 'value' => 's_corp'],
        ['name' => 'Partnership', 'value' => 'partnership'],
        ['name' => 'Trust/Estate', 'value' => 'trust_estate'],
        ['name' => 'Limited liability company.', 'value' => 'llc'],
        ['name' => 'Other', 'value' => 'other']
    ]);
    $address = factory(Address::class)->make();
    return array_merge($user_data, [
        'uses_ein_number' => false,
        'ssn' => $ssn,
        'title' => $faker->randomElement(['CNA', 'LPN', 'RN']),
        'hire_date' => $faker->date(),
        'gender' => $faker->randomElement([null, null, 'M', 'F']),
        'w9_name' => $user_data['firstname'] . ' ' . $user_data['lastname'],
        'w9_business_name' => $faker->company,
        'w9_tax_classification' => $tax_classifications->pluck('value')->random(),
        'w9_address' => $address->address1,
        'w9_city_state_zip' => $address->city . ' ' . $address->state . ' ' . $address->zip
    ]);
});
