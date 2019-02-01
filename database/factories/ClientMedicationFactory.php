<?php

use Faker\Generator as Faker;
use App\ClientMedication;
use App\Client;

$factory->define(ClientMedication::class, function (Faker $faker) {
    return [
        'client_id' => factory(Client::class)->create()->id,
        'name' => $faker->sentence,
        'dose' => $faker->sentence,
        'type' => $faker->word,
        'frequency' => $faker->randomDigit,
        'description' => $faker->paragraph,
        'side_effects' => $faker->randomElement([$faker->sentence, null]),
        'notes' => $faker->randomElement([$faker->paragraph, null]),
    ];
});
