<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBusinessClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('business_clients', function(Blueprint $table)
		{
			$table->foreign('business_id', 'fk_business_clients_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('client_id', 'fk_business_clients_client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('business_clients', function(Blueprint $table)
		{
			$table->dropForeign('fk_business_clients_business_id');
			$table->dropForeign('fk_business_clients_client_id');
		});
	}

}
