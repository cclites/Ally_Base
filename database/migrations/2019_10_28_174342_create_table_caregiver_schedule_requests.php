<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCaregiverScheduleRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'caregiver_schedule_requests', function ( Blueprint $table ) {

            $table->increments( 'id' );
            $table->unsignedInteger( 'business_id' )->index()->nullable();
            $table->unsignedInteger( 'caregiver_id' )->index()->nullable();
            $table->unsignedInteger( 'schedule_id' )->index()->nullable();
            $table->enum( 'status', [ 'pending', 'denied', 'cancelled', 'approved' ])->default( 'pending' )->index();
            $table->timestamps();

            $table->foreign( 'business_id' )
                ->references( 'id' )
                ->on( 'businesses' )
                ->onDelete( 'CASCADE' );

            $table->foreign( 'schedule_id' )
                ->references( 'id' )
                ->on( 'schedules' )
                ->onDelete( 'CASCADE' );

            $table->foreign( 'caregiver_id' )
                ->references( 'id' )
                ->on( 'caregivers' )
                ->onDelete( 'set null' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'caregiver_schedule_requests' );
    }
}
