<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddRoutingNumbersToGatewayTransations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->smallInteger('routing_number')->nullable();
            $table->smallInteger('account_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->dropColum(['routing_number', 'account_number']);
        });
    }
}
