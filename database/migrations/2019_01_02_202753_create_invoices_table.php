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

        Schema::create('client_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_invoice_id');

            $table->string('invoiceable_type');
            $table->string('invoiceable_id');
            $table->string('group')->nullable();
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('rate', 7, 2); // client rate
            $table->decimal('total', 9, 2); // client rate x units
            $table->decimal('amount_due', 9, 2); // total x payer allocation
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('amount_due', 9, 2);
            $table->unsignedInteger('client_id')->nullable();
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->unsignedInteger('business_id')->nullable();
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoiceable_type');
            $table->string('invoiceable_id');
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('rate', 7, 2);
            $table->decimal('total', 9, 2);
            $table->decimal('amount_due', 9, 2);
        });

        Schema::create('payers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('npi_number')->nullable();
            $table->unsignedInteger('chain_id');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('chain_id')->nullable(); // If null, services are global
            $table->timestamps();
        });

        Schema::create('payer_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payer_id');
            $table->unsignedInteger('service_id');
            $table->date('effective_start');
            $table->date('effective_end');
            $table->decimal('hourly_rate', 7, 2);
            $table->decimal('fixed_rate', 7, 2);
            $table->timestamps();
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

        Schema::create('schedule_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->decimal('duration', 7, 2);
            $table->decimal('client_rate', 7, 2);
            $table->decimal('caregiver_rate', 7, 2);
            $table->timestamps();
        });

        Schema::create('shift_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('payer_id')->nullable();
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->decimal('duration', 7, 2);
            $table->decimal('client_rate', 7, 2);
            $table->decimal('caregiver_rate', 7, 2);
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
        Schema::dropIfExists('invoices');
    }
}
