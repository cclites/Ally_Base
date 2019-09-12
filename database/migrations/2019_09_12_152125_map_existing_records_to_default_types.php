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
                $caregiverIds = $chain->caregivers()->select('caregivers.id')->get()->pluck('id');

                $chain->expirationTypes->each(function (ExpirationType $expirationType) use ($caregiverIds) {
                    CaregiverLicense::whereIn('caregiver_id', $caregiverIds)
                        ->where('name','=', $expirationType->type)
                        ->update(['chain_expiration_type_id' => $expirationType->id]);
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
