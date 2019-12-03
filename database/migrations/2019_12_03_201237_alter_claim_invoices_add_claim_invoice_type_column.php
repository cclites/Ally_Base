<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimInvoicesAddClaimInvoiceTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->string('claim_invoice_type', 32)->default(\App\Claims\ClaimInvoiceType::SINGLE())->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->dropColumn(['claim_invoice_type']);
        });
    }
}
