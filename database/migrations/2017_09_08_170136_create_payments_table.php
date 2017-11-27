<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('client_id')->unsigned()->index('fk_payments_client_id_idx');
			$table->integer('caregiver_id')->unsigned()->index('fk_payments_caregiver_id_idx');
			$table->integer('business_id')->unsigned()->index('fk_payments_business_id_idx');
//			$table->string('reference_type', 45)->nullable();
//			$table->string('reference_id', 45)->nullable();
			$table->string('method_type', 45)->nullable();
			$table->string('method_id', 45)->nullable();
			$table->decimal('amount', 9)->nullable();
			$table->string('transaction_id')->nullable();
			$table->string('transaction_code')->nullable();
			$table->boolean('success')->nullable()->default(1);
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
		Schema::drop('payments');
	}

}
