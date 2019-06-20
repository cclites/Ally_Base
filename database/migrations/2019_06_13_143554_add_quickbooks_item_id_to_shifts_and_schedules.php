<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuickbooksItemIdToShiftsAndSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedInteger('quickbooks_service_id')->nullable()->after('payer_id');
        });

        Schema::table('shift_services', function (Blueprint $table) {
            $table->unsignedInteger('quickbooks_service_id')->nullable()->after('ally_rate');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('quickbooks_service_id')->nullable()->after('added_to_past');
        });

        Schema::table('schedule_services', function (Blueprint $table) {
            $table->unsignedInteger('quickbooks_service_id')->nullable()->after('caregiver_rate');
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
            $table->dropColumn('quickbooks_service_id');
        });
        Schema::table('shift_services', function (Blueprint $table) {
            $table->dropColumn('quickbooks_service_id');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('quickbooks_service_id');
        });
        Schema::table('schedule_services', function (Blueprint $table) {
            $table->dropColumn('quickbooks_service_id');
        });
    }
}
