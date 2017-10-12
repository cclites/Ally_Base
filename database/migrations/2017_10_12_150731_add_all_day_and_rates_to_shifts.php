<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllDayAndRatesToShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('all_day')->default(false);
            $table->decimal('caregiver_rate')->default(0);
            $table->decimal('provider_fee')->default(0);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('all_day')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['all_day', 'caregiver_rate', 'provider_fee']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('all_day');
        });
    }
}
