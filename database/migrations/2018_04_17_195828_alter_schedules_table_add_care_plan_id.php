<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSchedulesTableAddCarePlanId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('care_plan_id')->nullable()->after('hours_type');

            $table->foreign('care_plan_id', 'fk_schedules_care_plan_id')
                ->references('id')
                ->on('care_plans')
                ->onDelete('cascade');
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
            $table->dropForeign('fk_schedules_care_plan_id');
            $table->dropColumn('care_plan_id');
        });
    }
}
