<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('addresses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('fk_addresses_user_id_idx');
			$table->string('type', 45)->nullable();
			$table->string('address1')->nullable();
			$table->string('address2')->nullable();
			$table->string('city', 45)->nullable();
			$table->string('state', 45)->nullable();
			$table->char('country', 2)->nullable()->default('US');
			$table->string('zip', 45)->nullable();
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
		Schema::drop('addresses');
	}

}
