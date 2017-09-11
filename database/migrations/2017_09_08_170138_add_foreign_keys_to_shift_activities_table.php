<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToShiftActivitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shift_activities', function(Blueprint $table)
		{
			$table->foreign('activity_id', 'fk_shift_activities_activity_id')->references('id')->on('activities')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('shift_id', 'fk_shift_activities_shift_id')->references('id')->on('shifts')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shift_activities', function(Blueprint $table)
		{
			$table->dropForeign('fk_shift_activities_activity_id');
			$table->dropForeign('fk_shift_activities_shift_id');
		});
	}

}
