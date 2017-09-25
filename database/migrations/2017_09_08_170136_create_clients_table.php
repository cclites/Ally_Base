<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clients', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->primary();
            $table->integer('business_id')->unsigned()->index('fk_business_clients_business_id_idx');
            $table->decimal('business_fee', 9)->nullable();
            $table->string('default_payment_type', 45)->nullable();
            $table->string('default_payment_id', 45)->nullable();
            $table->string('backup_payment_type', 45)->nullable();
            $table->string('backup_payment_id', 45)->nullable();
            $table->foreign('business_id', 'fk_clients_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('clients');
	}

}
