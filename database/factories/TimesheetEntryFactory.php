<?php

use Faker\Generator as Faker;
use App\TimesheetEntry;

$factory->define(TimesheetEntry::class, function (Faker $faker) {
    $duration = mt_rand(60, 720);
    $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));

    return [
        // 'timesheet_id' => factory('App\Timesheet')->create()->id,
        'checked_in_time' => $start,
        'checked_out_time' => $end,
        'mileage' => $faker->numberBetween(0, 50),
        'other_expenses' => $faker->numberBetween(0, 15),
        'caregiver_comments' => $faker->sentence($faker->numberBetween(0, 5)),
        'caregiver_rate' => $faker->numberBetween(8, 20),
        'provider_fee' => $faker->numberBetween(1, 3),
    ];
});
