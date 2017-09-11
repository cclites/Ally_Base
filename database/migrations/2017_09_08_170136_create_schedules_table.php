<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('schedules', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('business_id')->unsigned()->index('fk_schedules_business_id_idx');
			$table->integer('caregiver_id')->unsigned()->nullable()->index('fk_schedules_caregiver_id_idx');
			$table->integer('client_id')->unsigned()->index('fk_schedules_client_id_idx');
			$table->date('start_date');
			$table->date('end_date');
			$table->time('time');
			$table->integer('duration')->unsigned();
			$table->string('rrule')->nullable();
			$table->text('notes', 65535)->nullable();
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
		Schema::drop('schedules');
	}

}
