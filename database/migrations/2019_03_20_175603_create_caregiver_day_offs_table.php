<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaregiverDayOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_days_off', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caregiver_id');
            $table->date('date');
            $table->string('description', 156);

            $table->timestamps();

            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_days_off');
    }
}
