<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCaregiverDaysOffDateRange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_days_off', function (Blueprint $table) {
            $table->renameColumn('date', 'start_date');
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_days_off', function (Blueprint $table) {
            $table->renameColumn('start_date', 'date');
            $table->dropColumn('end_date');
        });
    }
}
