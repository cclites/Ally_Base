<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Claims\ClaimStatus;
use App\Claims\ClaimInvoice;

class AlterClaimInvoicesTableAddTransmittedAtColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->timestamp('transmitted_at')->nullable()->after('modified_at');
        });

        // Migrate timestamp for any existing transmitted claims
        ClaimInvoice::with('statuses')
            ->whereHas('statuses', function ($q) {
                $q->where('status', ClaimStatus::TRANSMITTED()->getValue());
            })
            ->get()
            ->each(function (ClaimInvoice $claim) {
                if ($status = $claim->statuses->where('status', ClaimStatus::TRANSMITTED()->getValue())->sortBy('created_at')->first()) {
                    $claim->transmitted_at = $status->created_at;
                    $claim->save();
                }
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
            $table->dropColumn(['transmitted_at']);
        });
    }
}
