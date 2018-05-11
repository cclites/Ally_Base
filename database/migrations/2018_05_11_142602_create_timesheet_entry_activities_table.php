<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetEntryActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheet_entry_activities', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('timesheet_entry_id');
            $table->unsignedInteger('activity_id');
            $table->timestamps();
            
            $table->foreign('activity_id', 'fk_timesheet_entry_activities_activity_id')
                ->references('id')
                ->on('activities')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreign('timesheet_entry_id', 'fk_timesheet_entry_activities_entry_id')
                ->references('id')
                ->on('timesheet_entries')
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
		Schema::table('timesheet_entry_activities', function(Blueprint $table)
		{
			$table->dropForeign('fk_timesheet_entry_activities_activity_id');
			$table->dropForeign('fk_timesheet_entry_activities_entry_id');
        });
        
        Schema::dropIfExists('timesheet_entry_activities');
    }
}
