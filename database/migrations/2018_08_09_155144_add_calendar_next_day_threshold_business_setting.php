<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCalendarNextDayThresholdBusinessSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function(Blueprint $table) {
            $table->time('calendar_next_day_threshold')->default('09:00:00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function(Blueprint $table) {
            $table->dropColumn('calendar_next_day_threshold');
        });
    }
}
