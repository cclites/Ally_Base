<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimInvoiceItemsRenameClientCaseManagerToServicesCoordinator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->renameColumn('client_case_manager', 'services_coordinator');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->renameColumn('services_coordinator', 'client_case_manager');
        });
    }
}
