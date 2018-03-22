<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_transactions', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->foreign('id')->references('id')->on('gateway_transactions')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_transactions');
    }
}
