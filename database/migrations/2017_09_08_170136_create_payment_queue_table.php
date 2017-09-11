<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_queue', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('client_id')->unsigned()->index('fk_payment_queue_client_id_idx');
			$table->integer('caregiver_id')->unsigned()->index('fk_payment_queue_caregiver_id_idx');
			$table->integer('business_id')->unsigned()->nullable()->index('fk_payment_queue_business_id_idx');
			$table->string('reference_type', 45)->nullable();
			$table->string('reference_id', 45)->nullable();
			$table->decimal('amount', 9)->nullable();
			$table->timestamps();
			$table->dateTime('process_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment_queue');
	}

}
