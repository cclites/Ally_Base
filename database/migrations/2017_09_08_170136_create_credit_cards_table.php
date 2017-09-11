<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCreditCardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('credit_cards', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('fk_credit_cards_user_id_idx');
			$table->string('nickname', 45)->nullable();
			$table->string('name_on_card', 45)->nullable();
			$table->binary('number', 65535)->nullable();
			$table->boolean('expiration_month')->nullable();
			$table->date('expiration_year')->nullable();
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
		Schema::drop('credit_cards');
	}

}
