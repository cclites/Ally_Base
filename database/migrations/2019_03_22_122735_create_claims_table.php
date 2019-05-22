<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_invoice_id');
            $table->decimal('amount', 9, 2)->default(0.0);
            $table->decimal('balance', 9, 2)->default(0.0);
            $table->string('status', 35)->index();
            $table->timestamps();

            $table->foreign('client_invoice_id')->references('id')->on('client_invoices')->onDelete('restrict')->onUpdate('cascade');
        });

        Schema::create('claim_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_id');
            $table->string('status', 35)->index();
            $table->timestamps();

            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('claim_payments');
        Schema::dropIfExists('claim_status_history');
        Schema::dropIfExists('claims');
    }
}
