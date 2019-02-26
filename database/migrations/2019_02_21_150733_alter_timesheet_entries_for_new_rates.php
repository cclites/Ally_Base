<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTimesheetEntriesForNewRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->decimal('client_rate')->after('caregiver_comments')->nullable();
            $table->decimal('caregiver_rate')->nullable()->default(null)->change();
            $table->decimal('provider_fee')->nullable()->default(null)->change(); // No longer used, just maintain for backwards compatibility
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->dropColumn(['client_rate']);
            $table->decimal('caregiver_rate')->nullable(false)->default(0.00)->change();
            $table->decimal('provider_fee')->nullable(false)->default(0.00)->change();
        });
    }
}
