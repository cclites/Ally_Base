<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M2mRelationsForInvoicesPaymentsAndDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('invoice_id');
            $table->decimal('amount_applied', 9, 2);

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('restrict');
            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('restrict');
        });

        Schema::create('invoice_deposits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_id');
            $table->string('invoice_type', 128);
            $table->unsignedInteger('invoice_id');
            $table->decimal('amount_applied', 9, 2);

            $table->foreign('deposit_id')->references('id')->on('deposits')->onDelete('restrict');
            $table->index(['invoice_type', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_deposits');
    }
}
