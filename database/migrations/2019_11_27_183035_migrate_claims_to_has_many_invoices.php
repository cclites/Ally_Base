<?php

use Illuminate\Database\Migrations\Migration;

class MigrateClaimsToHasManyInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('claim_invoices')
            ->orderBy('id')
            ->chunk(400, function ($chunk) {
                $chunk->each(function ($claim) {
                    \DB::table('claim_invoice_client_invoice')
                        ->insert([
                            'claim_invoice_id' => $claim->id,
                            'client_invoice_id' => $claim->client_invoice_id,
                        ]);

                    \DB::table('claim_invoice_items')
                        ->where('claim_invoice_id', $claim->id)
                        ->update(['client_invoice_id' => $claim->client_invoice_id]);
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('claim_invoice_client_invoice')->truncate();
        \DB::table('claim_invoice_items')->update(['client_invoice_id' => null]);
    }
}
