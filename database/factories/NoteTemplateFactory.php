<?php

use Faker\Generator as Faker;
use App\NoteTemplate;
use App\Business;
use App\OfficeUser;

$factory->define(NoteTemplate::class, function (Faker $faker) {
    return [
        'business_id' => function () {
            return factory(Business::class)->create()->id;
        },
        'note' => $faker->paragraph,
        'short_name' => Str::limit($faker->sentence, 32),
        'active' => $faker->boolean,
        'created_by' => function() {
            return factory(OfficeUser::class)->create()->id;
        }
    ];
});
