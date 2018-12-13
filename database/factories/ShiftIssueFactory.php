<?php

use Faker\Generator as Faker;
use App\ShiftIssue;
use App\Shift;

$factory->define(ShiftIssue::class, function(Faker $faker) {
    return [
        'shift_id' => function () {
            return factory(Shift::class)->create()->id;
        },
        'client_injury' => $faker->randomElement([0,0,1]),
        'caregiver_injury' => $faker->randomElement([0,0,1]),
        'comments' => (mt_rand(0,1) === 0) ? $faker->paragraph : null,
    ];
});