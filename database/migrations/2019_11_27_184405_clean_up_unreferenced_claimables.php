<?php

use Illuminate\Database\Migrations\Migration;
use App\Claims\ClaimableService;

class CleanUpUnreferencedClaimables extends Migration
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

        // Clean up any left over claimable data that is no longer attached to a parent claim item
        ClaimableService::whereNotIn('id', \DB::table('claim_invoice_items')->select('claimable_id')->get()->pluck('claimable_id'))->delete();
        \App\Claims\ClaimableExpense::whereNotIn('id', \DB::table('claim_invoice_items')->select('claimable_id')->get()->pluck('claimable_id'))->delete();

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
