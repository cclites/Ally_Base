<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarePlanActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('care_plan_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('care_plan_id');
            $table->unsignedInteger('activity_id');

            $table->foreign('care_plan_id')->references('id')->on('care_plans')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('care_plan_activities');
    }
}
