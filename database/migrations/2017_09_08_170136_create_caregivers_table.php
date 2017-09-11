<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCaregiversTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('caregivers', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->primary();
			$table->binary('ssn', 65535)->nullable();
			$table->integer('bank_account_id')->unsigned()->nullable()->index('fk_caregivers_bank_account_id_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('caregivers');
	}

}
