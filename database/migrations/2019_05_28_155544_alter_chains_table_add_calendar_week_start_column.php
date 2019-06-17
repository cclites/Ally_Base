<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChainsTableAddCalendarWeekStartColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->unsignedTinyInteger('calendar_week_start')->default(1)->after('enable_schedule_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->dropColumn('calendar_week_start');
        });
    }
}
