<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsTableAddMedicarePlanAndPayerIdentifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('medicaid_plan_id')->nullable()->after('medicaid_diagnosis_codes');
            $table->string('medicaid_payer_id')->nullable()->after('medicaid_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('medicaid_plan_id');
            $table->dropColumn('medicaid_payer_id');
        });
    }
}
