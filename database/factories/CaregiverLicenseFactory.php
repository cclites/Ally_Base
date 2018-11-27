<?php

use \App\CaregiverLicense;
use \App\Caregiver;
use \Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(CaregiverLicense::class, function (Faker $faker) {
    return [
        'caregiver_id' => factory(Caregiver::class)->create()->id,
        'name' => $faker->word,
        'description' => $faker->sentence,
        'expires_at' => Carbon::now()->addDays($faker->numberBetween(1, 100))
    ];
});
