<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Shift::class, function(Faker $faker) {
    $duration = mt_rand(90, 240);
    $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));
    $latitude = $faker->randomFloat(4, 32, 46);
    $longitude = $faker->randomFloat(4, -118, -74);

    return [
        'checked_in_time' => $start,
        'checked_in_latitude' => $latitude,
        'checked_in_longitude' => $longitude,
        'checked_in_number' => null,
        'checked_out_time' => $end,
        'checked_out_latitude' => ($end) ? $latitude : null,
        'checked_out_longitude' => ($end) ? $longitude : null,
        'checked_out_number' => null,
    ];
});

$factory->define(App\ShiftIssue::class, function(Faker $faker) {
    return [
        'client_injury' => $faker->randomElement([0,0,1]),
        'caregiver_injury' => $faker->randomElement([0,0,1]),
        'comments' => (mt_rand(0,1) == 0) ? $faker->paragraph : null,
    ];
});