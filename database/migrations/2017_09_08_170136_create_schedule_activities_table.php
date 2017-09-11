<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScheduleActivitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('schedule_activities', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('schedule_id')->unsigned()->index('fk_schedule_activities_schedule_id_idx');
			$table->integer('activity_id')->unsigned()->index('fk_schedule_activities_activity_id_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('schedule_activities');
	}

}
