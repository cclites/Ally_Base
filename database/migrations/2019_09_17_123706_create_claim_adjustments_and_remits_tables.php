<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimAdjustmentsAndRemitsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_remits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->date('date');
            $table->string('payment_type', 25);
            $table->unsignedInteger('payer_id')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('amount', 9, 2)->default(0.00);
            $table->decimal('amount_applied', 9, 2)->default(0.00);
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claim_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('claim_remit_id')->nullable();
            $table->unsignedBigInteger('claim_invoice_id')->nullable();
            $table->unsignedBigInteger('claim_invoice_item_id')->nullable();
            $table->string('adjustment_type', 30);
            $table->decimal('amount_applied', 9, 2)->default(0.00);
            $table->boolean('is_interest')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('claim_remit_id')->references('id')->on('claim_remits')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('claim_invoice_item_id')->references('id')->on('claim_invoice_items')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_adjustments');
        Schema::dropIfExists('claim_remits');
    }
}
