<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRateOverridesToSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->renameColumn('scheduled_rate', 'caregiver_rate');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->decimal('provider_fee')->nullable()->after('caregiver_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->renameColumn('caregiver_rate', 'scheduled_rate');
            $table->dropColumn('provider_fee');
        });
    }
}
