<?php

use Faker\Generator as Faker;
use App\Billing\Payments\Methods\CreditCard;
use App\Client;

$factory->define(CreditCard::class, function (Faker $faker) {
    return [
        'user_id' => factory(Client::class)->create()->user->id,
        'nickname' => $faker->word,
        'name_on_card' => $faker->name,
        'number' => $faker->creditCardNumber,
        'expiration_month' => $faker->creditCardExpirationDate->format('m'),
        'expiration_year' => $faker->creditCardExpirationDate->format('Y'),
        'type' => 'visa',
    ];
});

$factory->state(CreditCard::class, 'amex', [
    'number' => '378734493671000',
    'type' => 'amex',
]);
