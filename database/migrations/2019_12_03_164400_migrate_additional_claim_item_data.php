<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateAdditionalClaimItemData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        \DB::beginTransaction();

        \App\Claims\ClaimInvoiceItem::with(['client', 'client.caseManager'])
            ->whereHas('client')
            ->chunk(400, function ($collection) {
                $collection->each(function (\App\Claims\ClaimInvoiceItem $item) {
                    // Note: client_program_number, client_cirts_number and client_invoice_notes
                    // do not need to be migrated here because those client fields are created after this migration
                    $item->client_case_manager = optional($item->client->caseManager)->name_last_first;
                    $item->client_ltci_policy_number = $item->client->getPolicyNumber();
                    $item->client_ltci_claim_number = $item->client->getClaimNumber();
                    $item->client_hic = $item->client->hic;
                    $item->save();
                });
            });

        \DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No turning back
    }
}
