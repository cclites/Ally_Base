<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreeFloatingScheduleNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'schedule_free_floating_notes', function ( Blueprint $table ) {

            $table->increments('id');
            $table->date('start_date')->index();
            $table->integer('business_id')->unsigned();
            $table->text( 'body' );
            $table->timestamps();

            $table->foreign( 'business_id' )->references( 'id' )->on( 'businesses' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'schedule_free_floating_notes' );
    }
}
