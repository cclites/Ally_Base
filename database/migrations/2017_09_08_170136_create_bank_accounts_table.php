<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBankAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bank_accounts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable()->index('fk_bank_accounts_user_id_idx');
			$table->string('nickname', 45)->nullable();
			$table->binary('routing_number', 65535)->nullable();
			$table->binary('account_number', 65535)->nullable();
			$table->string('account_type', 45)->nullable()->default('Checking');
			$table->boolean('verified')->nullable()->default(1);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bank_accounts');
	}

}
