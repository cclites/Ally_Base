<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessCaregiversTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('business_caregivers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('caregiver_id')->unsigned()->index('fk_business_caregivers_caregiver_id_idx');
			$table->integer('business_id')->unsigned();
			$table->string('type', 45)->nullable()->default('Contractor');
			$table->unique(['business_id','caregiver_id'], 'business_caregivers_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('business_caregivers');
	}

}
