<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCreditCardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('credit_cards', function(Blueprint $table)
		{
			$table->foreign('user_id', 'fk_credit_cards_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('credit_cards', function(Blueprint $table)
		{
			$table->dropForeign('fk_credit_cards_user_id');
		});
	}

}
