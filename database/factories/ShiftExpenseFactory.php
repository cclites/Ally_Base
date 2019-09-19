<?php

use App\Billing\Invoiceable\ShiftExpense;
use App\Shift;
use Faker\Generator as Faker;

$factory->define( ShiftExpense::class, function ( Faker $faker ) {

    if( !Shift::exists() ) $shift = factory( Shift::class )->create();
    else $shift = Shift::inRandomOrder()->first();

    return [

        'shift_id' => $shift->id,
        'name'     => $faker->name,
        'units'    => $faker->numerify( '###.##' ),
        'rate'     => $faker->numerify( '###.####' ),
        'ally_fee' => $faker->numerify( '###.##' ),
        'notes'    => $faker->paragraph
    ];
});
