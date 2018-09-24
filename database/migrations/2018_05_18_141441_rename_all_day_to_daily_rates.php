<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAllDayToDailyRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function(Blueprint $table) {
            $table->boolean('daily_rates')->default(0)->after('note_id');
        });

        Schema::table('shifts', function(Blueprint $table) {
            $table->renameColumn('all_day', 'daily_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function(Blueprint $table) {
            $table->dropColumn('daily_rates');
        });

        Schema::table('shifts', function(Blueprint $table) {
            $table->renameColumn('daily_rates', 'all_day');
        });
    }
}
