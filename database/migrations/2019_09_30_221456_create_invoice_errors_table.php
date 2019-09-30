<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_errors', function (Blueprint $table) {

            $table->bigIncrements( 'id' );

            // maybe this should be a morph relationship? for extensibility for other invoices?
            $table->unsignedInteger( 'client_invoice_id' );

            $table->integer( 'error_batch_id' );
            $table->string( 'error_text', 255 );

            $table->timestamps();

            // maybe this should be a morph relationship? for extensibility for other invoices?
            $table->foreign( 'client_invoice_id' )->references( 'id' )->on( 'client_invoices' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'invoice_errors' );
    }
}
