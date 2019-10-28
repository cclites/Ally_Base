<?php

use Faker\Generator as Faker;
use App\Note;
use App\Business;
use App\Caregiver;
use App\Client;
use App\OfficeUser;

$factory->define(Note::class, function (Faker $faker) {
    return [
        'business_id' => function () {
            return factory(Business::class)->create()->id;
        },
        'caregiver_id' => function() {
            return factory(Caregiver::class)->create()->id;
        },
        'client_id' => function () {
            return factory(Client::class)->create()->id;
        },
        'body' => $faker->paragraph,
        'title' => $faker->word,
        'tags' => $faker->word,
        'created_by' => function() {
            return factory(OfficeUser::class)->create()->id;
        }
    ];
});
