<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDepositsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('deposits', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('deposit_type', 45);
			$table->integer('caregiver_id')->unsigned()->nullable()->index('fk_deposits_caregiver_id_idx');
			$table->integer('business_id')->unsigned()->nullable()->index('fk_deposits_business_id_idx');
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
		Schema::drop('deposits');
	}

}
