<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimableExpensesTableAddCaregiverColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->nullable()->after('shift_id');
            $table->string('caregiver_first_name', 35)->default('')->after('caregiver_id');
            $table->string('caregiver_last_name', 35)->default('')->after('caregiver_first_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->dropColumn([
                'caregiver_id',
                'caregiver_first_name',
                'caregiver_last_name',
            ]);
        });
    }
}
