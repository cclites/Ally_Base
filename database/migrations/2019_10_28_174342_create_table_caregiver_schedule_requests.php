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
            $table->unsignedInteger( 'business_id' )->index();
            $table->unsignedInteger( 'client_id' )->index();
            $table->unsignedInteger( 'caregiver_id' )->index();
            $table->unsignedInteger( 'schedule_id' )->index();
            $table->string( 'status', 50 )->default( 'pending' )->index();
            $table->timestamps();

            $table->foreign( 'business_id' )
                ->references( 'id' )
                ->on( 'businesses' )
                ->onDelete( 'CASCADE' );

            $table->foreign( 'schedule_id' )
                ->references( 'id' )
                ->on( 'schedules' )
                ->onDelete( 'CASCADE' );

            $table->foreign( 'client_id' )
                ->references( 'id' )
                ->on( 'clients' )
                ->onDelete( 'CASCADE' );

            $table->foreign( 'caregiver_id' )
                ->references( 'id' )
                ->on( 'caregivers' )
                ->onDelete( 'CASCADE' );
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
