<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('businesses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 64)->unique();
			$table->string('type', 45)->default('Registry');
			$table->integer('bank_account_id')->unsigned()->nullable()->index('fk_businesses_bank_account_id_idx');
			$table->boolean('active')->nullable();
			$table->string('address1')->nullable();
			$table->string('address2')->nullable();
			$table->string('city', 45)->nullable();
			$table->string('state', 45)->nullable();
			$table->string('zip', 45)->nullable();
			$table->char('country', 2)->nullable()->default('US');
			$table->string('phone1', 45)->nullable();
			$table->string('phone2', 45)->nullable();
			$table->decimal('default_commission_rate', 5)->nullable();
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
		Schema::drop('businesses');
	}

}
