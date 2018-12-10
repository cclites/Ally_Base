<?php

use Faker\Generator as Faker;
use App\Task;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(),
        'notes' => $faker->paragraph(),
        'due_date' => $faker->dateTimeBetween('now', '+7 days'),
    ];
});
