<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksConnectionsTableAddClientTypeFeeTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->string('fee_type_lead_agency', 25)->default('registry')->after('name_format');
            $table->string('fee_type_ltci', 25)->default('registry')->after('fee_type_lead_agency');
            $table->string('fee_type_medicaid', 25)->default('registry')->after('fee_type_ltci');
            $table->string('fee_type_private_pay', 25)->default('registry')->after('fee_type_medicaid');
            $table->string('fee_type_va', 25)->default('registry')->after('fee_type_private_pay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->dropColumn([
                'fee_type_lead_agency',
                'fee_type_ltci',
                'fee_type_medicaid',
                'fee_type_private_pay',
                'fee_type_va',
            ]);
        });
    }
}
