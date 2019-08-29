<?php

use App\VisitEditCode;
use Illuminate\Database\Seeder;

class VisitEditCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach( VisitEditCode::VISIT_EDIT_CODES[ 'actions' ] as $code => $description ){

            factory( VisitEditCode::class )->create([

                'code'        => $code,
                'description' => $description,
                'type'        => 'action'
            ]);
        }

        foreach( VisitEditCode::VISIT_EDIT_CODES[ 'reasons' ] as $code => $description ){

            factory( VisitEditCode::class )->create([

                'code'        => $code,
                'description' => $description,
                'type'        => 'reason'
            ]);
        }
    }
}
