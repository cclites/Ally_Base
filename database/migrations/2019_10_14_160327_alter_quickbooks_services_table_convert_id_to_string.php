<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksServicesTableConvertIdToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        if (\App\QuickbooksService::whereNull('service_id')->exists()) {
            throw new Exception('Cannot migrate quickbooks service id because there are null values in the database.');
        }

        Schema::table('quickbooks_services', function (Blueprint $table) {
            $table->string('service_id', 30)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbooks_services', function (Blueprint $table) {
            $table->unsignedInteger('service_id')->nullable()->change();
        });
    }
}
