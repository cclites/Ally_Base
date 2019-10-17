<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksCustomersTableConvertIdToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        if (config('app.env') != 'production') {
            // If in a dev environment just clear out the old rare blank customer IDs
            \App\QuickbooksCustomer::whereNull('customer_id')->delete();
        }

        if (\App\QuickbooksCustomer::whereNull('customer_id')->exists()) {
            throw new Exception('Cannot migrate quickbooks customer id because there are null values in the database.');
        }

        Schema::table('quickbooks_customers', function (Blueprint $table) {
            $table->string('customer_id', 30)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No turning back
    }
}
