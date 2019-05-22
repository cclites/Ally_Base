<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksConnectionsTableAddGeneralMappingSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->string('name_format')->default('last_first')->after('access_token');
            $table->unsignedInteger('shift_service_id')->nullable()->after('name_format');
            $table->unsignedInteger('adjustment_service_id')->nullable()->after('shift_service_id');
            $table->unsignedInteger('refund_service_id')->nullable()->after('adjustment_service_id');
            $table->unsignedInteger('mileage_service_id')->nullable()->after('refund_service_id');
            $table->unsignedInteger('expense_service_id')->nullable()->after('mileage_service_id');
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
            $table->dropColumn('shift_service_id');
            $table->dropColumn('adjustment_service_id');
            $table->dropColumn('refund_service_id');
            $table->dropColumn('mileage_service_id');
            $table->dropColumn('expense_service_id');
            $table->dropColumn('name_format');
        });
    }
}
