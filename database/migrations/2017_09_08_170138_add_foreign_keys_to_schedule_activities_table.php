<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScheduleActivitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('schedule_activities', function(Blueprint $table)
		{
			$table->foreign('activity_id', 'fk_schedule_activities_activity_id')->references('id')->on('activities')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('schedule_id', 'fk_schedule_activities_schedule_id')->references('id')->on('schedules')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('schedule_activities', function(Blueprint $table)
		{
			$table->dropForeign('fk_schedule_activities_activity_id');
			$table->dropForeign('fk_schedule_activities_schedule_id');
		});
	}

}
