<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChainExpirationTypeToCaregiverLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'caregiver_licenses', function ( Blueprint $table ) {

            $table->unsignedInteger( 'chain_expiration_type_id' )->nullable();

            $table->foreign( 'chain_expiration_type_id' )->references( 'id' )->on( 'chain_expiration_types' )->onDelete( 'set null' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'caregiver_licenses', function ( Blueprint $table ) {

            $table->dropForeign( 'caregiver_licenses_chain_expiration_type_id_foreign' );
            $table->dropColumn( 'chain_expiration_type_id' );
        });
    }
}
