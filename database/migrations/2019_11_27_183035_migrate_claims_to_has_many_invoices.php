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
                $chunk->each(function ($invoice) {
                    \DB::table('claim_invoice_client_invoice')
                        ->insert([
                            'claim_invoice_id' => $invoice->id,
                            'client_invoice_id' => $invoice->client_invoice_id,
                        ]);
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
    }
}
