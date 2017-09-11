<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBusinessOfficeUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('business_office_users', function(Blueprint $table)
		{
			$table->foreign('business_id', 'fk_business_office_users_business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('office_user_id', 'fk_business_office_users_user_id')->references('id')->on('office_users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('business_office_users', function(Blueprint $table)
		{
			$table->dropForeign('fk_business_office_users_business_id');
			$table->dropForeign('fk_business_office_users_user_id');
		});
	}

}
