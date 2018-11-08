<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRateCodesToSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('provider_fee_id')->nullable()->after('fixed_rates');
            $table->unsignedInteger('client_rate_id')->nullable()->after('fixed_rates');
            $table->unsignedInteger('caregiver_rate_id')->nullable()->after('fixed_rates');
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
            $table->dropColumn(['caregiver_rate_id', 'client_rate_id', 'provider_fee_id']);
        });
    }
}
