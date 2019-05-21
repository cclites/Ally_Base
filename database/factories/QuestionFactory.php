<?php

use Faker\Generator as Faker;
use App\ClientType;
use Illuminate\Support\Arr;
use App\Question;
use App\Business;

$factory->define(Question::class, function (Faker $faker) {
    return [
        'business_id' => function() {
            $business = Business::inRandomOrder()->first();
            if ($business) {
                return $business->id;
            }
            return factory(Business::class)->create()->id;
        },
        'question' => $faker->sentence() . '?',
        'required' => false,
        'client_type' => Arr::random(ClientType::all()),
    ];
});

$factory->state(Question::class, 'required', function(Faker $faker) {
    return [
        'business_id' => function() {
            $business = Business::inRandomOrder()->first();
            if ($business) {
                return $business->id;
            }
            return factory(Business::class)->create()->id;
        },
        'question' => $faker->sentence() . '?',
        'required' => true,
        'client_type' => Arr::random(ClientType::all()),
    ];
});
