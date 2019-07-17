<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use  App\Billing\Payments\PaymentDescriptionTypes;

class AlterAddDescriptionColumnToOfflineInvoicePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offline_invoice_payments', function (Blueprint $table) {
            $table->dropColumn(['description']);
            $table->enum('description', PaymentDescriptionTypes::PAYMENT_DESCRIPTIONS)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offline_invoice_payments', function (Blueprint $table) {
            $table->dropColumn(['description']);
        });
    }
}
