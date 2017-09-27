<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewayTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gateway_id');
            $table->string('transaction_id')->index();
            $table->string('transaction_type');
            $table->decimal('amount', 9, 2);
            $table->boolean('success');
            $table->boolean('declined')->default(0);
            $table->boolean('cvv_pass')->nullable();
            $table->boolean('avs_pass')->nullable();
            $table->string('response_text')->nullable();
            $table->text('response_data')->nullable();
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
        Schema::dropIfExists('gateway_transactions');
    }
}
