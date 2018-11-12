<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientRateFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->decimal('client_rate', 8, 2)->after('provider_fee')->nullable();
        });

        Schema::table('client_caregivers', function(Blueprint $table) {
            $table->unsignedInteger('caregiver_hourly_id')->after('caregiver_id')->nullable();
            $table->unsignedInteger('caregiver_fixed_id')->after('caregiver_hourly_rate')->nullable();
            $table->unsignedInteger('provider_hourly_id')->after('caregiver_fixed_rate')->nullable();
            $table->unsignedInteger('provider_fixed_id')->after('provider_hourly_fee')->nullable();
            $table->unsignedInteger('client_hourly_id')->after('provider_fixed_fee')->nullable();
            $table->decimal('client_hourly_rate', 8, 2)->after('provider_fixed_fee')->nullable();
            $table->unsignedInteger('client_fixed_id')->after('provider_fixed_fee')->nullable();
            $table->decimal('client_fixed_rate', 8, 2)->after('provider_fixed_fee')->nullable();
        });

        Schema::table('client_caregivers', function(Blueprint $table) {
            $table->decimal('caregiver_hourly_rate', 8, 2)->nullable()->change();
            $table->decimal('caregiver_fixed_rate', 8, 2)->nullable()->change();
            $table->decimal('provider_hourly_fee', 8, 2)->nullable()->change();
            $table->decimal('provider_fixed_fee', 8, 2)->nullable()->change();
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
            $table->dropColumn('client_rate');
        });

        Schema::table('client_caregivers', function (Blueprint $table) {
            $table->dropColumn(['caregiver_hourly_id', 'caregiver_fixed_id', 'provider_hourly_id', 'provider_fixed_id', 'client_hourly_id', 'client_hourly_rate', 'client_fixed_id', 'client_fixed_rate']);
        });

        Schema::table('client_caregivers', function(Blueprint $table) {
            $table->decimal('caregiver_hourly_rate',8, 2)->default('0.00')->nullable(false)->change();
            $table->decimal('caregiver_fixed_rate', 8, 2)->default('0.00')->nullable(false)->change();
            $table->decimal('provider_hourly_fee', 8, 2)->default('0.00')->nullable(false)->change();
            $table->decimal('provider_fixed_fee', 8, 2)->default('0.00')->nullable(false)->change();
        });
    }
}
