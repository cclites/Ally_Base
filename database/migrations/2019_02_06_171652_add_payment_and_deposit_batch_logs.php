<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentAndDepositBatchLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_id', 32);
            $table->unsignedInteger('chain_id')->nullable();
            $table->unsignedInteger('payment_id')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->string('exception')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index('batch_id');
            $table->foreign('chain_id')->references('id')->on('business_chains');
            $table->foreign('payment_id')->references('id')->on('payments');
        });

        Schema::create('deposit_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_id', 32);
            $table->unsignedInteger('chain_id')->nullable();
            $table->unsignedInteger('deposit_id')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->string('exception')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index('batch_id');
            $table->foreign('chain_id')->references('id')->on('business_chains');
            $table->foreign('deposit_id')->references('id')->on('deposits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_log');
        Schema::dropIfExists('deposit_log');
    }
}
