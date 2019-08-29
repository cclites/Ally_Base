<?php

use App\VisitEditCode;
use Faker\Generator as Faker;

$factory->define( VisitEditCode::class, function ( Faker $faker ) {

    return [

        'type'        => $faker->randomElement( [ 'reason', 'action' ] ),
        'description' => $faker->paragraph( 4 ),
        'code'        => $faker->numerify( '###' )
    ];
});
