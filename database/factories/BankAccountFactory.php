<?php

use Faker\Generator as Faker;
use App\OfficeUser;
use App\Business;
use App\Billing\Payments\Methods\BankAccount;

$factory->define(BankAccount::class, function (Faker $faker) {
    return [
        'user_id' => factory(OfficeUser::class)->create()->user->id,
        'business_id' => factory(Business::class)->create()->id,
        'nickname' => $faker->word,
        'name_on_account' => $faker->name,
        'routing_number' => $this->faker->randomNumber(9, true),
        'account_number' => $faker->bankAccountNumber,
        'account_type' => collect('checking', 'savings')->random(),
        'account_holder_type' => collect('business', 'personal')->random(),
        'verified' => $faker->boolean

    ];
});