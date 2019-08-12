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
            $table->bigIncrements('id');

            $table->unsignedInteger('business_id');
            $table->unsignedInteger('client_invoice_id');
            $table->string('name', 255)->unique();
            $table->decimal('amount', 9,2)->default(0.00);
            $table->decimal('amount_due', 9,2)->default(0.00);
            $table->string('status', 35);
            $table->string('transmission_method', 35)->nullable();

            $table->unsignedInteger('client_id');
            $table->string('client_first_name', 45);
            $table->string('client_last_name', 45);
            $table->date('client_dob')->nullable();
            $table->string('client_medicaid_id', 255)->nullable();
            $table->string('client_medicaid_diagnosis_codes', 255)->nullable();

            $table->unsignedInteger('payer_id');
            $table->string('payer_name', 255);
            $table->string('payer_code', 255)->nullable();
            $table->string('plan_code', 255)->nullable();

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('client_invoice_id')->references('id')->on('client_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claim_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('claim_invoice_id');
            $table->morphs('invoiceable'); // links to the original invoiceable item from the Client Invoice.
            $table->morphs('claimable'); // links to the new, editable claimable object
            $table->decimal('rate', 9, 2);
            $table->decimal('units', 9, 2);
            $table->decimal('amount', 9, 2);
            $table->decimal('amount_due', 9, 2);

            $table->timestamps();

            $table->foreign('claim_invoice_id')->references('id')->on('claim_invoices')->onDelete('CASCADE')->onUpdate('CASCADE');
        });

        Schema::create('claimable_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('shift_id')->nullable();
            $table->string('name');
            $table->dateTime('date');
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claimable_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('shift_id')->nullable();

            $table->unsignedInteger('caregiver_id');
            $table->string('caregiver_first_name', 35);
            $table->string('caregiver_last_name', 35);
            $table->char('caregiver_gender', 1)->nullable();
            $table->date('caregiver_dob')->nullable();
            $table->binary('caregiver_ssn')->nullable();
            $table->string('caregiver_medicaid_id', 255)->nullable();

            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('city', 45)->nullable();
            $table->string('state', 45)->nullable();
            $table->string('zip', 45)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->dateTime('visit_start_time');
            $table->dateTime('visit_end_time');
            $table->dateTime('evv_start_time');
            $table->dateTime('evv_end_time');
            $table->string('checked_in_number', 45)->nullable();
            $table->string('checked_out_number', 45)->nullable();
            $table->decimal('checked_in_latitude', 10, 7)->nullable();
            $table->decimal('checked_in_longitude', 10, 7)->nullable();
            $table->decimal('checked_out_latitude', 10, 7)->nullable();
            $table->decimal('checked_out_longitude', 10, 7)->nullable();
            $table->boolean('has_evv')->default(false);
            $table->string('evv_method_in', 255)->nullable();
            $table->string('evv_method_out', 255)->nullable();

            $table->unsignedInteger('service_id');
            $table->string('service_name', 70);
            $table->string('service_code', 30)->nullable();

            $table->text('activities')->nullable();
            $table->text('caregiver_comments')->nullable();

            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('RESTRICT')->onUpdate('CASCADE');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claimable_expenses');
        Schema::dropIfExists('claimable_services');
        Schema::dropIfExists('claim_invoice_items');
        Schema::dropIfExists('claim_invoices');
    }
}
