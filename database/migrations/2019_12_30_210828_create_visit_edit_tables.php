<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitEditTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'visit_edit_reasons', function ( Blueprint $table ) {

            $table->unsignedBigInteger( 'code' );
            $table->string( 'description', 255 );
            $table->timestamps();

            $table->primary( 'code' );
        });
        Schema::create( 'visit_edit_actions', function ( Blueprint $table ) {

            $table->unsignedBigInteger( 'code' );
            $table->string( 'description', 255 );
            $table->timestamps();

            $table->primary( 'code' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'visit_edit_reasons' );
        Schema::dropIfExists( 'visit_edit_actions' );
    }
}
