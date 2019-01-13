<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('payer_id')->nullable(); // null if private pay?

            $table->timestamps();
        });

        Schema::create('caregiver_invoices', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->unsignedInteger('caregiver_id');

            $table->timestamps();
        });

        Schema::create('business_invoices', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->unsignedInteger('business_id');

            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice_type');
            $table->unsignedInteger('invoice_id');
            $table->string('invoiceable_type');
            $table->unsignedInteger('invoiceable_id');
            $table->string('group')->nullable();
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('rate', 7, 2); // client rate
            $table->decimal('total', 9, 2); // client rate x units
            $table->decimal('amount_due', 9, 2); // total x payer allocation
            $table->dateTime('date')->nullable(); // used for ordering

            $table->index(['invoice_type', 'invoice_id']);
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });

        Schema::create('payers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('npi_number')->nullable();
            $table->unsignedTinyInteger('week_start')->default(0);
			$table->string('address1')->nullable();
			$table->string('address2')->nullable();
			$table->string('city', 45)->nullable();
			$table->string('state', 45)->nullable();
			$table->string('zip', 45)->nullable();
            $table->string('phone_number', 45)->nullable();
            $table->string('fax_number', 45)->nullable();
            $table->unsignedInteger('chain_id');
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('payer_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payer_id');
            $table->unsignedInteger('service_id')->nullable();
            $table->date('effective_start');
            $table->date('effective_end');
            $table->decimal('hourly_rate', 7, 2);
            $table->decimal('fixed_rate', 7, 2);
            $table->timestamps();

            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('client_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->date('effective_start');
            $table->date('effective_end');
            $table->decimal('caregiver_hourly_rate', 7, 2);
            $table->decimal('caregiver_fixed_rate', 7, 2);
            $table->decimal('client_hourly_rate', 7, 2);
            $table->decimal('client_fixed_rate', 7, 2);
            $table->timestamps();
        });

        Schema::create('client_payers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->string('policy_number')->nullable();
            $table->date('effective_start');
            $table->date('effective_end');
            $table->string('payment_allocation')->nullable();
            $table->decimal('payment_allowance', 9, 2)->nullable();
            $table->decimal('split_percentage', 3, 2)->nullable();
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();
        });

        Schema::create('schedule_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->string('hours_type')->default('default');
            $table->decimal('duration', 7, 2);
            $table->decimal('client_rate', 7, 2);
            $table->decimal('caregiver_rate', 7, 2);
            $table->timestamps();
        });

        Schema::create('shift_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->string('hours_type')->default('default');
            $table->decimal('duration', 7, 2);
            $table->decimal('client_rate', 7, 2);
            $table->decimal('caregiver_rate', 7, 2);
            $table->decimal('ally_rate', 7, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('shift_adjustments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('caregiver_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->unsignedInteger('shift_id')->nullable();
            $table->decimal('units', 7, 2);
            $table->decimal('client_rate', 7, 2);
            $table->decimal('caregiver_rate', 7, 2);
            $table->decimal('ally_rate', 7, 2)->nullable();
            $table->string('status');
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
        Schema::dropIfExists('shift_adjustments');
        Schema::dropIfExists('shift_services');
        Schema::dropIfExists('schedule_services');
        Schema::dropIfExists('client_payers');
        Schema::dropIfExists('client_rates');
        Schema::dropIfExists('payer_rates');
        Schema::dropIfExists('payers');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('business_invoices');
        Schema::dropIfExists('caregiver_invoices');
        Schema::dropIfExists('client_invoices');
    }
}
