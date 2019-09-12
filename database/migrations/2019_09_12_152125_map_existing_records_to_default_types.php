<?php

use App\BusinessChain;
use App\Caregiver;
use App\CaregiverLicense;
use App\ExpirationType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MapExistingRecordsToDefaultTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // iterate over each chain,
        // then iterate over all its expirations
        // then run query update caregivers for matching chain with a string match to the exp id

        ExpirationType::where( 'chain_id', '!=', null )
            ->get()
            ->groupBy( 'chain_id' )
            ->each( function( $chain, $chain_id ){

                $caregivers = Caregiver::forChains( $chain_id )
                    ->with( 'licenses' )
                    ->pluck( 'id' );

                DB::table( 'caregiver_licenses as exp' )
                    ->rightJoin( 'chain_expiration_types as exp_type', 'exp.name', '=', 'exp_type.type' )
                    ->select( 'exp_type.id as exp_type_id', 'exp.id as exp_id' )
                    ->whereIn( 'caregiver_id', $caregivers )
                    ->get()
                    ->each( function( $record ){

                        CaregiverLicense::where( 'id', $record->exp_id )
                            ->update([

                                'chain_expiration_type_id' => $record->exp_type_id
                            ]);
                    });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        CaregiverLicense::all()->update([

            'chain_expiration_type_id' => null
        ]);
    }
}
