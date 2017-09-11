<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShiftIssuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shift_issues', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('shift_id')->unsigned()->index('fk_shift_issues_shift_id_idx');
			$table->boolean('client_injury')->default(0);
			$table->boolean('caregiver_injury')->default(0);
			$table->text('comments', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shift_issues');
	}

}
