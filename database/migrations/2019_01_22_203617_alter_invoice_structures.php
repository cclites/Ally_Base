<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInvoiceStructures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoice_items');

        Schema::table('client_invoices', function (Blueprint $table) {
            $table->decimal('amount', 9, 2)->default(0);
            $table->decimal('amount_paid', 9, 2)->default(0);

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('restrict');
        });

        Schema::table('caregiver_invoices', function (Blueprint $table) {
            $table->decimal('amount', 9, 2)->default(0);
            $table->decimal('amount_paid', 9, 2)->default(0);

            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('restrict');
        });

        Schema::table('business_invoices', function (Blueprint $table) {
            $table->decimal('amount', 9, 2)->default(0);
            $table->decimal('amount_paid', 9, 2)->default(0);

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('restrict');
        });

        Schema::create('client_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id');
            $table->string('invoiceable_type')->nullable();
            $table->unsignedInteger('invoiceable_id')->nullable();
            $table->string('group')->nullable();
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('rate', 7, 2); // client rate
            $table->decimal('total', 9, 2); // client rate x units
            $table->decimal('amount_due', 9, 2); // total x payer allocation
            $table->dateTime('date')->nullable(); // used for ordering
            $table->string('notes')->nullable();

            $table->foreign('invoice_id')->references('id')->on('client_invoices')->onDelete('cascade');
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });

        Schema::create('caregiver_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id');
            $table->string('invoiceable_type')->nullable();
            $table->unsignedInteger('invoiceable_id')->nullable();
            $table->string('group')->nullable();
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('rate', 7, 2); // caregiver rate
            $table->decimal('total', 9, 2); // caregiver rate x units
            $table->dateTime('date')->nullable(); // used for ordering
            $table->string('notes')->nullable();

            $table->foreign('invoice_id')->references('id')->on('caregiver_invoices')->onDelete('cascade');
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });

        Schema::create('business_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id');
            $table->string('invoiceable_type')->nullable();
            $table->unsignedInteger('invoiceable_id')->nullable();
            $table->string('group')->nullable();
            $table->string('name');
            $table->decimal('units', 7, 2);
            $table->decimal('client_rate', 7, 2); // informational
            $table->decimal('caregiver_rate', 7, 2); // informational
            $table->decimal('ally_rate', 7, 2); // informational
            $table->decimal('rate', 7, 2); // provider rate
            $table->decimal('total', 9, 2); // provider rate x units
            $table->dateTime('date')->nullable(); // used for ordering
            $table->string('notes')->nullable();

            $table->foreign('invoice_id')->references('id')->on('business_invoices')->onDelete('cascade');
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
