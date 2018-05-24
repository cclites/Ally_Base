<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('timesheet_id');
            $table->dateTime('checked_in_time');
            $table->dateTime('checked_out_time');
			$table->decimal('mileage', 9)->default(0.00);
			$table->decimal('other_expenses', 9)->default(0.00);
			$table->text('caregiver_comments', 65535)->nullable();
            $table->decimal('caregiver_rate')->default(0.00);
            $table->decimal('provider_fee')->default(0.00);

            $table->foreign('timesheet_id', 'fk_timesheet_entries_timesheet_id')
                ->references('id')
                ->on('timesheets')
                ->onUpdate('CASCADE')
                ->onDelete('RESTRICT');

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
		Schema::table('timesheet_entries', function(Blueprint $table)
		{
			$table->dropForeign('fk_timesheet_entries_timesheet_id');
        });
        
        Schema::dropIfExists('timesheet_entries');
    }
}
