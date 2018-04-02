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

if (!function_exists('userFactory')) {
    function userFactory(Faker $faker) {
        $email = $faker->unique()->safeEmail;
        return [
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => $email,
            'username' => $email,
            'password' => $password = bcrypt('demo'),
            'remember_token' => str_random(10),
            'date_of_birth' => $faker->date('Y-m-d', '-20 years'),
        ];
    }
}

$factory->define(\App\Admin::class, function(Faker $faker) {
    return array_merge(userFactory($faker), []);
});

$factory->define(\App\Caregiver::class, function(Faker $faker) {
    $user_data = userFactory($faker);
    $ssn = $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4);
    return array_merge($user_data, [
        'ssn' => $ssn,
        'title' => $faker->randomElement(['CNA', 'LPN', 'RN']),
        'hire_date' => $faker->date(),
        'gender' => $faker->randomElement([null, null, 'M', 'F']),
    ]);
});

$factory->state(\App\Caregiver::class, 'w9', function(Faker $faker) {
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
    $address = factory(\App\Address::class)->make();
    return array_merge($user_data, [
        'ssn' => $ssn,
        'title' => $faker->randomElement(['CNA', 'LPN', 'RN']),
        'hire_date' => $faker->date(),
        'gender' => $faker->randomElement([null, null, 'M', 'F']),
        'w9_name' => $user_data['firstname'] . ' ' . $user_data['lastname'],
        'w9_business_name' => $faker->company,
        'w9_tax_classification' => $tax_classifications->pluck('value')->random(),
        'w9_ssn' => $ssn,
        'w9_address' => $address->address1,
        'w9_city_state_zip' => $address->city . ' ' . $address->state . ' ' . $address->zip
    ]);
});

$factory->define(\App\Client::class, function(Faker $faker) {
    if (!\App\Business::exists()) {
        // Create a business if one does not exist.  Client's need to be attached to a business.
        factory(\App\Business::class)->create();
    }
    return array_merge(userFactory($faker), [
        'ssn' => $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4),
        'business_id' => \App\Business::inRandomOrder()->value('id'),
        'client_type' => $faker->randomElement(['private_pay', 'medicaid', 'LTCI']),
        'onboard_status' => $faker->randomElement([null, 'needs_agreement', 'reconfirmed_checkbox']),
        'dr_first_name' => $faker->firstName,
        'dr_last_name' => $faker->lastName,
        'dr_phone' => $faker->phoneNumber,
        'dr_fax' => $faker->phoneNumber
    ]);
});

$factory->define(\App\OfficeUser::class, function(Faker $faker) {
    return array_merge(userFactory($faker), []);
});

$factory->define(\App\Address::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['billing', 'evv', 'home']),
        'address1' => $faker->streetAddress,
        'address2' => $faker->randomElement([null, 'Apt' . mt_rand(1,2000), 'Suite #' . mt_rand(100,200)]),
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5)
    ];
});

$factory->define(\App\PhoneNumber::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['primary', 'primary', 'billing']),
        'number' => $faker->phoneNumber
    ];
});

$factory->define(\App\CreditCard::class, function(Faker $faker) {
    return [
        'nickname' => $faker->streetName,
        'name_on_card' => $faker->name,
        'number' => $faker->creditCardNumber,
        'type' => $faker->randomElement(['visa', 'mastercard', 'amex']),
        'expiration_month' => mt_rand(1,12),
        'expiration_year' => mt_rand(date('Y'), date('Y') + 3),
    ];
});

$factory->define(\App\BankAccount::class, function(Faker $faker) {
    return [
        'nickname' => $name = $faker->name,
        'name_on_account' => $name,
        'routing_number' => mt_rand(100000000,999999999),
        'account_number' => $faker->bankAccountNumber,
        'account_type' => 'checking',
        'account_holder_type' => $faker->randomElement(['personal', 'business']),
        'verified' => $faker->randomElement([0,1,1])
    ];
});