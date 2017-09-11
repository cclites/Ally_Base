<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('business_clients', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('client_id')->unsigned()->index('fk_business_clients_client_id_idx');
			$table->integer('business_id')->unsigned()->index('fk_business_clients_business_id_idx');
			$table->decimal('business_fee', 9)->nullable();
			$table->string('default_payment_type', 45)->nullable();
			$table->string('default_payment_id', 45)->nullable();
			$table->string('backup_payment_type', 45)->nullable();
			$table->string('backup_payment_id', 45)->nullable();
			$table->timestamps();
			$table->unique(['client_id','business_id'], 'business_clients_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('business_clients');
	}

}
