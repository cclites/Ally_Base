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
    return array_merge(userFactory($faker), [
        'ssn' => $faker->randomNumber(3) . '-' . $faker->randomNumber(2) . '-' . $faker->randomNumber(4),
    ]);
});

$factory->define(\App\Client::class, function(Faker $faker) {
    if (!\App\Business::first()) {
        factory(\App\Business::class, 3)->create();
    }
    return array_merge(userFactory($faker), [
        'business_id' => \App\Business::inRandomOrder()->value('id'),
        'business_fee' => mt_rand(100,900) / 100,
    ]);
});

$factory->define(\App\OfficeUser::class, function(Faker $faker) {
    return array_merge(userFactory($faker), []);
});

$factory->define(\App\Address::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['billing', 'mailing']),
        'address1' => $faker->streetAddress,
        'address2' => null,
        'city' => $faker->city,
        'state' => $faker->randomElement(['CA', 'OH', 'NY', 'MI', 'PA', 'FL', 'TX', 'WA']),
        'country' => 'US',
        'zip' => $faker->randomNumber(5)
    ];
});

$factory->define(\App\PhoneNumber::class, function(Faker $faker) {
    return [
        'type' => $faker->randomElement(['home', 'mobile', 'work']),
        'number' => $faker->phoneNumber
    ];
});

$factory->define(\App\CreditCard::class, function(Faker $faker) {
    return [
        'nickname' => $faker->streetName,
        'name_on_card' => $faker->name,
        'number' => $faker->creditCardNumber,
        'expiration_month' => mt_rand(1,12),
        'expiration_year' => mt_rand(date('Y'), date('Y') + 3),
    ];
});

$factory->define(\App\BankAccount::class, function(Faker $faker) {
    return [
        'nickname' => $faker->streetName,
        'name_on_account' => $faker->streetName,
        'routing_number' => $faker->bankAccountNumber,
        'account_number' => $faker->bankAccountNumber,
        'account_type' => 'Checking',
        'verified' => $faker->randomElement([0,1,1])
    ];
});