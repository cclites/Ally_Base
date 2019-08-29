<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitEditCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'visit_edit_codes', function ( Blueprint $table ) {

            $table->increments( 'id' );
            $table->integer( 'code' );
            $table->string( 'type' );
            $table->text( 'description' );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'visit_edit_codes' );
    }
}
