<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDepositsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('deposits', function(Blueprint $table)
		{
			$table->foreign('business_id', 'fk_deposits_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('caregiver_id', 'fk_deposits_caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('deposits', function(Blueprint $table)
		{
			$table->dropForeign('fk_deposits_business_id');
			$table->dropForeign('fk_deposits_caregiver_id');
		});
	}

}
