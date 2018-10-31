<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('issued_transaction_id');
            $table->unsignedInteger('issued_payment_id');
            $table->unsignedInteger('refunded_transaction_id');
            $table->unsignedInteger('refunded_payment_id');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_refunds');
    }
}
