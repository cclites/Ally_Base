<?php

use Faker\Generator as Faker;
use App\CustomField;
use App\CustomFieldOption;

$factory->define(CustomFieldOption::class, function (Faker $faker) {

    if (!CustomField::exists()) {
        // Create a custom dropdown field if one does not exist.
        factory(CustomField::class)->create(['type' => 'dropdown']);
    }

    return [
        'field_id' => CustomField::inRandomOrder()->first()->id,
        'value' => snake_case($faker->word),
        'label' => $faker->word,
    ];
});
