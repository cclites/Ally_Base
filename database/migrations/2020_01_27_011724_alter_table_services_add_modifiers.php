<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableServicesAddModifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'services', function ( Blueprint $table ) {

            $table->string( 'mod1', 10 )->nullable();
            $table->string( 'mod2', 10 )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'services', function ( Blueprint $table ) {

            $table->dropColumn([ 'mod1', 'mod2' ]);
        });
    }
}
