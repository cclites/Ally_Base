<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksClientInvoicesTableAddStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_client_invoices', function (Blueprint $table) {
            $table->text('errors')->nullable()->after('quickbooks_invoice_id');
            $table->string('status', 30)->default('transferred')->after('errors');
            $table->string('quickbooks_invoice_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbooks_client_invoices', function (Blueprint $table) {
            $table->dropColumn(['errors', 'status']);
            $table->string('quickbooks_invoice_id')->nullable(false)->change();
        });
    }
}
