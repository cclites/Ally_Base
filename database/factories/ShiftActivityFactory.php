<?php

use Faker\Generator as Faker;
use App\ShiftActivity;
use App\Shift;
use App\Activity;

$factory->define(ShiftActivity::class, function(Faker $faker) {
    return [
        'shift_id' => function () {
            return factory(Shift::class)->create()->id;
        },
        'activity_id' => function () {
            return factory(Activity::class)->create()->id;
        },
        'completed' => $faker->boolean
    ];
});