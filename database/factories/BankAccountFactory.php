<?php

use Faker\Generator as Faker;

$factory->define(BankAccount::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\OfficeUser::class)->create()->user->id;
        },
        'business_id' => function () {
            return factory(\App\Business::class)->create()->id;
        },
        'nickname' => $faker->word,
        'name_on_account' => $faker->name,
        'routing_number' => $this->faker->randomNumber(9),
        'account_number' => $faker->bankAccountNumber,
        'account_type' => collect('checking', 'savings')->random(),
        'account_holder_type' => collect('business', 'personal')->random(),
        'verified' => $faker->boolean

    ];
});
