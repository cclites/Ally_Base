<?php

use Faker\Generator as Faker;
use App\CustomField;
use App\BusinessChain;

$factory->define(CustomField::class, function (Faker $faker) {

    if (!BusinessChain::exists()) {
        // Create a business chain if one does not exist.
        factory(BusinessChain::class)->create();
    }

    return [
        'chain_id' => BusinessChain::inRandomOrder()->first()->id,
        'type' => $faker->randomElement(['input', 'radio', 'textarea']),
        'key' => $faker->word,
        'label' => $faker->word,
        'required' => $faker->boolean,
        'default_value' => function(array $field) {
            if(!$field['required']) return null;

            if($field['type'] == 'input') {
                return $faker->word;
            }else if($field['type'] == 'textarea') {
                return $faker->paragraph;
            }else if($field['type'] == 'radio') {
                return (string) $faker->boolean;
            }else if($field['type'] == 'dropdown') {
                return 0; // TOOD: create custom field options model and update this
            }
        },
    ];
});
