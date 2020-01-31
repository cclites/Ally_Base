<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\App\Knowledge::class, function (Faker $faker) {
    $title = join(' ', $faker->words(random_int(2, 5)));

    return [
        'type' => $faker->randomElement(['tutorial', 'faq', 'resource']),
        'title' => $title,
        'slug' => Str::slug($title),
        'body' => $faker->paragraph(),
    ];
});
