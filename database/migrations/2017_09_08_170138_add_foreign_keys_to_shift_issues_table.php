<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToShiftIssuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shift_issues', function(Blueprint $table)
		{
			$table->foreign('shift_id', 'fk_shift_issues_shift_id')->references('id')->on('shifts')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shift_issues', function(Blueprint $table)
		{
			$table->dropForeign('fk_shift_issues_shift_id');
		});
	}

}
