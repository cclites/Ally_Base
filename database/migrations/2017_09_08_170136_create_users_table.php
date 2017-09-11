<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('email')->unique('email_unique');
			$table->string('password');
			$table->string('firstname', 45)->nullable();
			$table->string('lastname', 45)->nullable();
			$table->date('date_of_birth')->nullable();
			$table->string('role_type', 45)->default('App\Client');
			$table->integer('access_group_id')->unsigned()->nullable();
			$table->boolean('active')->default(1);
			$table->string('remember_token')->nullable();
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
		Schema::drop('users');
	}

}
