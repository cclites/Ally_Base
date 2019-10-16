<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksClientInvoicesAddQuickbooksDesktopIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_client_invoices', function (Blueprint $table) {
            $table->string('qb_desktop_id', 35)->nullable()->after('quickbooks_invoice_id');
            $table->renameColumn('quickbooks_invoice_id', 'qb_online_id');
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
            $table->renameColumn('qb_online_id', 'quickbooks_invoice_id');
            $table->dropColumn(['qb_desktop_id']);
        });
    }
}
