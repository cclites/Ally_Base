<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableBusinessChainAddOpenShiftsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'businesses', function ( Blueprint $table ) {

            $table->dropColumn( 'open_shifts_setting' );
        });

        Schema::table( 'business_chains', function ( Blueprint $table ) {

            $table->string( 'open_shifts_setting', 50 )->default( 'off' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'businesses', function (Blueprint $table) {

            $table->string( 'open_shifts_setting', 50 )->default( 'off' )->after( 'timezone' );
        });

        Schema::table( 'business_chains', function ( Blueprint $table ) {

            $table->dropColumn([ 'open_shifts_setting' ]);
        });
    }
}
