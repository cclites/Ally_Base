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
			$table->binary('routing_number', 65535);
			$table->binary('account_number', 65535);
			$table->string('account_type', 45)->default('checking');
			$table->string('account_holder_type', 45)->default('personal');
			$table->string('name_on_account');
			$table->boolean('verified')->default(0);
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
