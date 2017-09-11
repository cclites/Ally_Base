<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessOfficeUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('business_office_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('office_user_id')->unsigned();
			$table->integer('business_id')->unsigned()->index('fk_business_office_users_business_id_idx');
			$table->unique(['office_user_id','business_id'], 'business_office_users_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('business_office_users');
	}

}
