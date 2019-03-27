<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeInvoicePaymentsUniqueToPreventMultipleApplicationsPerCombo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->unique(['invoice_id', 'payment_id']);
        });

        Schema::table('invoice_deposits', function (Blueprint $table) {
            $table->unique(['invoice_type', 'invoice_id', 'deposit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropUnique(['invoice_id', 'payment_id']);
        });

        Schema::table('invoice_deposits', function (Blueprint $table) {
            $table->dropUnique(['invoice_type', 'invoice_id', 'deposit_id']);
        });
    }
}
