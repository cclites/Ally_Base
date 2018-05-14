<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
			$table->unsignedInteger('caregiver_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('creator_id');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('denied_at')->nullable();
            $table->timestamps();

            $table->foreign('business_id', 'fk_timesheets_business_id')
                ->references('id')
                ->on('businesses')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreign('caregiver_id', 'fk_timesheets_caregiver_id')
                ->references('id')
                ->on('caregivers')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreign('client_id', 'fk_timesheets_client_id')
                ->references('id')
                ->on('clients')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

            $table->foreign('creator_id', 'fk_timesheets_creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('timesheets', function(Blueprint $table)
		{
            $table->dropForeign('fk_timesheets_business_id');
            $table->dropForeign('fk_timesheets_caregiver_id');
            $table->dropForeign('fk_timesheets_client_id');
        });
        
        Schema::dropIfExists('timesheets');
    }
}
