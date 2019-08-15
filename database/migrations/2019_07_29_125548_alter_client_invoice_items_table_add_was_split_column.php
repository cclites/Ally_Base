<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientInvoiceItemsTableAddWasSplitColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_invoice_items', function (Blueprint $table) {
            $table->boolean('was_split')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['was_split']);
        });
    }
}
