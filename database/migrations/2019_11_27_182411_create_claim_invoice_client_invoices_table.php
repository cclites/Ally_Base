<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoiceClientInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoice_client_invoice', function (Blueprint $table) {
            $table->primary(['claim_invoice_id', 'client_invoice_id'], 'claim_invoice_client_invoice_pk');
            $table->unsignedBigInteger('claim_invoice_id');
            $table->unsignedInteger('client_invoice_id');

            $table->foreign('claim_invoice_id', 'claim_invoice_client_invoice_claim_fk')->references('id')->on('claim_invoices')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('client_invoice_id', 'claim_invoice_client_invoice_client_fk')->references('id')->on('client_invoices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_invoice_client_invoice');
    }
}
