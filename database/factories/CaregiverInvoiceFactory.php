<?php

use Faker\Generator as Faker;

$factory->define(\App\Billing\CaregiverInvoice::class, function (Faker $faker) {
    return [
        'caregiver_id' => function() {
            $caregiver = \App\Caregiver::inRandomOrder()->first() ?? factory(\App\Caregiver::class)->create();
            return $caregiver->id;
        },
        'name' => mt_rand(),
    ];
});
