<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('starts_at');
            $table->date('end_date');
            $table->string('rrule');
            $table->string('interval_type');
            $table->timestamps();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('schedule_groups')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id']);
        });

        Schema::dropIfExists('schedule_groups');
    }
}
