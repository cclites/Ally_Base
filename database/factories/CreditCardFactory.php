<?php

use Faker\Generator as Faker;

$factory->define(\App\CreditCard::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(\App\Client::class)->create()->user->id;
        },
        'nickname' => $faker->word,
        'name_on_card' => $faker->name,
        'type' => $faker->creditCardType,
        'number' => $faker->creditCardNumber,
        'expiration_month' => $faker->creditCardExpirationDate->format('m'),
        'expiration_year' => $faker->creditCardExpirationDate->format('Y')
    ];
});
