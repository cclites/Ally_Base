<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShiftsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shifts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('caregiver_id')->unsigned()->nullable()->index('fk_shifts_caregiver_id_idx');
			$table->integer('client_id')->unsigned()->nullable()->index('fk_shifts_client_id_idx');
			$table->integer('business_id')->unsigned()->nullable()->index('fk_shifts_business_id_idx');
			$table->dateTime('checked_in_time')->nullable();
			$table->decimal('checked_in_latitude', 11, 8)->nullable();
			$table->decimal('checked_in_longitude', 11, 8)->nullable();
			$table->string('checked_in_number', 45)->nullable()->comment('evv phone number');
			$table->dateTime('checked_out_time')->nullable();
			$table->decimal('checked_out_latitude', 11, 8)->nullable();
			$table->decimal('checked_out_longitude', 11, 8)->nullable();
			$table->string('checked_out_number', 45)->nullable()->comment('evv phone number');
			$table->text('caregiver_comments', 65535)->nullable();
			$table->string('hours_type', 45)->nullable()->default('default');
			$table->decimal('mileage', 9)->default(0.00);
			$table->decimal('other_expenses', 9)->default(0.00);
			$table->boolean('paid')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shifts');
	}

}
