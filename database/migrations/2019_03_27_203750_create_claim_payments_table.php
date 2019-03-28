<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_id');
            $table->decimal('amount', 9, 2);
            $table->string('reference')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('payed_at');
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
        Schema::dropIfExists('claim_payments');
    }
}
