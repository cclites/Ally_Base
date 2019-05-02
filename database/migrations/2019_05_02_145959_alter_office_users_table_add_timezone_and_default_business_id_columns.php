<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOfficeUsersTableAddTimezoneAndDefaultBusinessIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_users', function (Blueprint $table) {
            $table->unsignedInteger('default_business_id')->nullable();
            $table->string('timezone')->default('America/New_York');

            $table->foreign('default_business_id')
                ->references('id')
                ->on('businesses')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('office_users', function (Blueprint $table) {
            $table->dropForeign(['default_business_id']);

            $table->dropColumn(['timezone', 'default_business_id']);
        });
    }
}
