<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClaimableServicesAddServiceModifers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'claimable_services', function ( Blueprint $table ) {

            $table->string( 'service_code_mod1', 10 )->after( 'service_code' )->nullable();
            $table->string( 'service_code_mod2', 10 )->after( 'service_code' )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'claimable_services', function ( Blueprint $table ) {

            $table->dropColumn([ 'service_code_mod1', 'service_code_mod2' ]);
        });
    }
}
