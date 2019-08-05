<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_invoices', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('business_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('client_invoice_id');
            $table->unsignedInteger('payer_id');
            $table->string('name', 255);
            $table->decimal('amount', 9,2);
            $table->decimal('amount_due', 9,2);
            $table->string('status', 35);
            $table->string('transmission_method', 35);

            $table->string('client_first_name', 255);
            $table->string('client_last_name', 255);
            $table->string('client_dob', 255);
            $table->string('client_medicaid_id', 255);
            $table->string('client_medicaid_diagnosis_codes', 255);
            $table->string('payer_name', 255);
            $table->string('payer_code', 255);
            $table->string('plan_code', 255);

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('client_invoice_id')->references('id')->on('client_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claim_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_invoice_id');
            $table->unsignedInteger('shift_id');
            $table->morphs('claimable');
            $table->unsignedInteger('caregiver_id');

            $table->decimal('amount', 9, 2);
            $table->decimal('amount_due', 9, 2);
            $table->decimal('rate', 9, 2);
            $table->decimal('duration', 9, 2);

            $table->string('caregiver_first_name', 255);
            $table->string('caregiver_last_name', 255);
            $table->string('caregiver_gender', 255);
            $table->string('caregiver_dob', 255);
            $table->string('caregiver_ssn', 255);
            $table->string('caregiver_medicaid_id', 255);
            $table->string('procedure_code', 255);

            $table->string('service_address_line1', 255);
            $table->string('service_address_line2', 255);
            $table->string('service_address_city', 255);
            $table->string('service_address_state', 255);
            $table->string('service_address_zip', 255);
            $table->string('service_address_latitude', 255);
            $table->string('service_address_longitude', 255);

            $table->string('scheduled_start_time', 255);
            $table->string('scheduled_end_time', 255);
            $table->string('visit_start_time', 255);
            $table->string('visit_end_time', 255);
            $table->string('evv_start_time', 255);
            $table->string('evv_end_time', 255);
            $table->string('checked_in_number', 255);
            $table->string('checked_in_latitude', 255);
            $table->string('checked_in_longitude', 255);
            $table->string('checked_out_number', 255);
            $table->string('checked_out_latitude', 255);
            $table->string('checked_out_longitude', 255);
            $table->string('has_evv', 255);
            $table->string('evv_method_in', 255);
            $table->string('evv_method_out', 255);
            $table->string('service_id', 255);
            $table->string('service_name', 255);
            $table->string('service_code', 255);
            $table->string('activities', 255);
            $table->string('caregiver_comments', 255);

            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('RESTRICT')->onUpdate('CASCADE');
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
        Schema::dropIfExists('claim_invoices');
    }
}
