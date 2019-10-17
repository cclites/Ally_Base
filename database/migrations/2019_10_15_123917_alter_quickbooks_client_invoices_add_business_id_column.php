<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksClientInvoicesAddBusinessIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_client_invoices', function (Blueprint $table) {
            $table->unsignedInteger('business_id')->nullable()->after('id');
        });

        $records = \DB::table('quickbooks_client_invoices')
            ->leftJoin('client_invoices', 'quickbooks_client_invoices.client_invoice_id', '=', 'client_invoices.id')
            ->leftJoin('clients', 'clients.id', '=', 'client_invoices.client_id')
            ->get(['quickbooks_client_invoices.id', 'clients.business_id']);

        foreach ($records as $record) {
            \DB::table('quickbooks_client_invoices')
                ->where('id', $record->id)
                ->update(['business_id' => $record->business_id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbooks_client_invoices', function (Blueprint $table) {
            $table->dropColumn(['business_id']);
        });
    }
}
