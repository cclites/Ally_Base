<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientReferralServiceAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_referral_service_agreements', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('client_id')->unique();
            $table->double('referral_fee');
            $table->double('per_visit_referral_fee');
            $table->double('per_visit_assessment_fee');
            $table->string('termination_notice');
            $table->string('executed_by');
            $table->text('payment_options');
            $table->string('agreement_file')->nullable();
            $table->binary('signature_one');
            $table->binary('signature_two');
            $table->binary('signature_client');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_referral_service_agreements');
    }
}
