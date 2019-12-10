<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimInvoiceTellusFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoice_tellus_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('claim_invoice_id');
            $table->string('filename');
            $table->string('status');
            $table->timestamps();

            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claim_invoice_tellus_file_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tellus_file_id');
            $table->dateTime('service_date')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('service_code')->nullable();
            $table->string('status_code');
            $table->text('import_status');
            $table->timestamps();

            $table->foreign('tellus_file_id')->references('id')->on('claim_invoice_tellus_files')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_invoice_tellus_file_results');
        Schema::dropIfExists('claim_invoice_tellus_files');
    }
}
