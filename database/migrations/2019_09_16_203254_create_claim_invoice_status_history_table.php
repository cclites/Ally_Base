<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoiceStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoice_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('claim_invoice_id');
            $table->string('status', 35)->index();
            $table->timestamps();

            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_invoice_status_history');
    }
}
