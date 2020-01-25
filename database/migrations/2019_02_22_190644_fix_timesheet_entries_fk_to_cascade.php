<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTimesheetEntriesFkToCascade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\DB::getDriverName() != 'sqlite') {
            Schema::table('timesheet_entries', function (Blueprint $table) {
                $table->dropForeign('fk_timesheet_entries_timesheet_id');
            });
        }

        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->foreign('timesheet_id', 'fk_timesheet_entries_timesheet_id')
                ->references('id')
                ->on('timesheets')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
