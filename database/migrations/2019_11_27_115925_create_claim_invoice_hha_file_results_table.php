<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoiceHhaFileResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoice_hha_file_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('hha_file_id');

            $table->dateTime('service_date')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('service_code')->nullable();
            $table->string('status_code');
            $table->string('import_status');

            $table->timestamps();

            $table->foreign('hha_file_id')->references('id')->on('claim_invoice_hha_files')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_invoice_hha_file_results');
    }
}
