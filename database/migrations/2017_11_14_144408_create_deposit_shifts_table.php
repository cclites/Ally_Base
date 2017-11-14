<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('deposit_payments');

        Schema::create('deposit_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_id')->references('id')->on('deposits')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedInteger('shifts_id')->references('id')->on('shifts')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
