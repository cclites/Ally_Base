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
        BusinessChain::select( 'id' )
            ->get()
            ->each( function( $chain ){
                // iterate through each chain

                $caregivers = Caregiver::forChains( $chain->id )
                    ->with( 'licenses' )
                    ->pluck( 'id' );
                    // grab all of its caregivers

                DB::table( 'caregiver_licenses as exp' )
                    ->rightJoin( 'chain_expiration_types as exp_type', 'exp.name', '=', 'exp_type.type' )
                    ->select( 'exp_type.id as exp_type_id', 'exp.id as exp_id', 'exp_type.chain_id' )
                    ->whereIn( 'caregiver_id', $caregivers )
                    ->where( 'exp_type.chain_id', $chain->id )
                    ->get()
                    ->each( function( $record ){
                        // join every existing license for all caregivers and match them to the chain's expiration types

                        CaregiverLicense::where( 'id', $record->exp_id )
                            ->update([
                                // then update each record that was matched

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
        DB::table( 'caregiver_licenses' )->update([

            'chain_expiration_type_id' => null
        ]);
    }
}
