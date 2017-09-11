<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBusinessCaregiversTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('business_caregivers', function(Blueprint $table)
		{
			$table->foreign('business_id', 'fk_business_caregivers_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('caregiver_id', 'fk_business_caregivers_caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('business_caregivers', function(Blueprint $table)
		{
			$table->dropForeign('fk_business_caregivers_business_id');
			$table->dropForeign('fk_business_caregivers_caregiver_id');
		});
	}

}
