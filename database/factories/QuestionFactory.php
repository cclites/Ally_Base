<?php

use Faker\Generator as Faker;
use App\ClientType;
use Illuminate\Support\Arr;
use App\Question;
use App\Business;

$factory->define(Question::class, function (Faker $faker) {
    $business = factory(Business::class)->create();

    return [
        'business_id' => $business->id,
        'question' => $faker->sentence() . '?',
        'required' => false,
        'client_type' => Arr::random(ClientType::all()),
    ];
});

$factory->state(Question::class, 'required', function(Faker $faker) {
    $business = factory(Business::class)->create();

    return [
        'business_id' => $business->id,
        'question' => $faker->sentence() . '?',
        'required' => true,
        'client_type' => Arr::random(ClientType::all()),
    ];
});
