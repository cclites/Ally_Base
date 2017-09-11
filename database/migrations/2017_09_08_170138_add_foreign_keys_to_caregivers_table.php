<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCaregiversTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('caregivers', function(Blueprint $table)
		{
			$table->foreign('bank_account_id', 'fk_caregivers_bank_account_id')->references('id')->on('bank_accounts')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('id', 'fk_caregivers_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('caregivers', function(Blueprint $table)
		{
			$table->dropForeign('fk_caregivers_bank_account_id');
			$table->dropForeign('fk_caregivers_id');
		});
	}

}
