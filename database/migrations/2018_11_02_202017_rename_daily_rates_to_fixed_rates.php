<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDailyRatesToFixedRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_caregivers', function (Blueprint $table) {
            $table->renameColumn('caregiver_daily_rate', 'caregiver_fixed_rate');
        });

        Schema::table('client_caregivers', function (Blueprint $table) {
            // Each rename has to be in a separate block for SQLite migration to work
            $table->renameColumn('provider_daily_fee', 'provider_fixed_fee');
        });

        Schema::table('schedules', function(Blueprint $table) {
            $table->renameColumn('daily_rates', 'fixed_rates');
        });

        Schema::table('shifts', function(Blueprint $table) {
            $table->renameColumn('daily_rates', 'fixed_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_caregivers', function (Blueprint $table) {
            $table->renameColumn('caregiver_fixed_rate', 'caregiver_daily_rate');
        });

        Schema::table('client_caregivers', function (Blueprint $table) {
            $table->renameColumn('provider_fixed_fee', 'provider_daily_fee');
        });

        Schema::table('schedules', function(Blueprint $table) {
            $table->renameColumn('fixed_rates', 'daily_rates');
        });

        Schema::table('shifts', function(Blueprint $table) {
            $table->renameColumn('fixed_rates', 'daily_rates');
        });
    }
}
