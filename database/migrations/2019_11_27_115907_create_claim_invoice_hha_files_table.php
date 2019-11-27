<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoiceHhaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoice_hha_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('claim_invoice_id');

            $table->string('filename');
            $table->string('status');

            $table->timestamps();

            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_invoice_hha_files');
    }
}
