<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAgendaweekToTimelineweek extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update all businesses to timeline week view
        \App\Business::query()->update(['calendar_default_view' => 'timelineWeek']);

        // temporary since enum column prevented changes to businesses
        // todo: change this to a schema method after removing the enum column
        if (DB::connection() instanceof  \Illuminate\Database\MySqlConnection) {
            DB::statement('ALTER TABLE `businesses`
	CHANGE COLUMN `calendar_default_view` `calendar_default_view` VARCHAR(255) NOT NULL DEFAULT \'timelineWeek\' COLLATE \'utf8mb4_unicode_ci\' AFTER `mileage_rate`;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
