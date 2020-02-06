<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCaregiverAvailabilityToAddTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_availability', function (Blueprint $table) {
            $table->time('available_start_time')->nullable();
            $table->time('available_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_availability', function (Blueprint $table) {
            $table->dropColumn(['available_start_time', 'available_end_time']);
        });
    }
}
