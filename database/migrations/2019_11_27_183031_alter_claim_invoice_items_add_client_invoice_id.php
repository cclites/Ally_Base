<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimInvoiceItemsAddClientInvoiceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->unsignedInteger('client_invoice_id')->nullable(true)->after('claim_invoice_id');

            $table->foreign('client_invoice_id', 'claim_invoice_items_client_invoice_fk')
                ->references('id')->on('client_invoices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->dropForeign('claim_invoice_items_client_invoice_fk');
        });

        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['client_invoice_id']);
        });
    }
}
