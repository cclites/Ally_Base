<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesTableAddPayrollPolicyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('pay_cycle')->nullable();
            $table->string('last_day_of_cycle')->nullable();
            $table->date('last_day_of_first_period')->nullable();
            $table->string('mileage_reimbursement_rate')->nullable();
            $table->text('unpaired_pay_rates')->nullable();
            $table->string('overtime_hours_day')->nullable();
            $table->string('overtime_hours_week')->nullable();
            $table->string('overtime_consecutive_days')->nullable();
            $table->string('dbl_overtime_hours_day')->nullable();
            $table->string('dbl_overtime_consecutive_days')->nullable();
            $table->string('overtime_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('pay_cycle');
            $table->dropColumn('last_day_of_cycle');
            $table->dropColumn('last_day_of_first_period');
            $table->dropColumn('mileage_reimbursement_rate');
            $table->dropColumn('unpaired_pay_rates');
            $table->dropColumn('overtime_hours_day');
            $table->dropColumn('overtime_hours_week');
            $table->dropColumn('overtime_consecutive_days');
            $table->dropColumn('dbl_overtime_hours_day');
            $table->dropColumn('dbl_overtime_consecutive_days');
            $table->dropColumn('overtime_method');
        });
    }
}
