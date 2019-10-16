<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuickbooksClientInvoiceStatusHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quickbooks_client_invoice_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quickbooks_client_invoice_id');
            $table->string('status', 35)->index();
            $table->timestamps();

            $table->foreign('quickbooks_client_invoice_id', 'qb_client_invoice_status_history_fk')
                ->references('id')
                ->on('quickbooks_client_invoices')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quickbooks_client_invoice_status_history');
    }
}
