<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksConnectionsTableRemoveOldFeeTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->dropColumn('fee_type');
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
            $table->string('fee_type', 25)->default('registry')->after('name_format');
        });
    }
}
