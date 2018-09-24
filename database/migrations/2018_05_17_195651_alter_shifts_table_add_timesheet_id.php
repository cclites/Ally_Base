<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftsTableAddTimesheetId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedInteger('timesheet_id')->nullable();
            $table->foreign('timesheet_id')->references('id')->on('timesheets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['timesheet_id']);
            $table->dropColumn('timesheet_id');
        });
    }
}
