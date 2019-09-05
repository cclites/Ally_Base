<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimRemitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_remits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->date('date');
            $table->string('payment_type', 25);
            $table->unsignedInteger('payer_id')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('amount', 9, 2)->default(0.00);
            $table->decimal('amount_applied', 9, 2)->default(0.00);
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_remits');
    }
}
