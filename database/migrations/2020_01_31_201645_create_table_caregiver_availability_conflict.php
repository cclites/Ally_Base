<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCaregiverAvailabilityConflict extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_availability_conflict', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger( 'caregiver_id' );
            $table->unsignedInteger( 'schedule_id' );
            $table->unsignedInteger( 'business_id' )->nullable();
            $table->dateTime('starts_at');
            $table->string('reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_availability_conflict');
    }
}
