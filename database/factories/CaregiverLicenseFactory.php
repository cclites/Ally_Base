<?php

use Faker\Generator as Faker;

$factory->define(\App\CaregiverLicense::class, function (Faker $faker) {
    return [
        'caregiver_id' => function () {
            return factory(\App\Caregiver::class)->create()->id;
        },
        'name' => $faker->word,
        'description' => $faker->sentence,
        'expires_at' => \Carbon\Carbon::now()->addDays($faker->numberBetween(1, 100))
    ];
});
