<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPaymentQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payment_queue', function(Blueprint $table)
		{
			$table->foreign('business_id', 'fk_payment_queue_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('caregiver_id', 'fk_payment_queue_caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('client_id', 'fk_payment_queue_client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payment_queue', function(Blueprint $table)
		{
			$table->dropForeign('fk_payment_queue_business_id');
			$table->dropForeign('fk_payment_queue_caregiver_id');
			$table->dropForeign('fk_payment_queue_client_id');
		});
	}

}
