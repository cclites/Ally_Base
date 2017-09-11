<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScheduleExceptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('schedule_exceptions', function(Blueprint $table)
		{
			$table->foreign('schedule_id', 'fk_schedule_exceptions_schedule_id')->references('id')->on('schedules')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('schedule_exceptions', function(Blueprint $table)
		{
			$table->dropForeign('fk_schedule_exceptions_schedule_id');
		});
	}

}
