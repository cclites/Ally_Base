<?php

use App\Claims\ClaimableService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use App\Claims\ClaimInvoiceItem;

class MigrateClaimClientDataToClaimableTables extends Migration
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
        ClaimInvoiceItem::with(['claimable', 'claim'])->chunk(400, function (Collection $items) {
            $items->each(function (ClaimInvoiceItem $item) {
                if (
                    empty($item->claim->client_id) ||
                    empty($item->claim->client_first_name) ||
                    empty($item->claim->client_last_name)
                ) {
                    throw new InvalidArgumentException("Cannot convert Claim #{$item->claim->id} because of blank client info.");
                }
                $data = [
                    'client_id' => $item->claim->client_id,
                    'client_first_name' => $item->claim->client_first_name,
                    'client_last_name' => $item->claim->client_last_name,
                    'client_dob' => $item->claim->client_dob,
                    'client_medicaid_id' => $item->claim->client_medicaid_id,
                    'client_medicaid_diagnosis_codes' => $item->claim->client_medicaid_diagnosis_codes,
                ];

                if ($item->claimable_type != ClaimableService::class) {
                    $data = array_only($data, ['client_id', 'client_first_name', 'client_last_name']);
                }

                $item->claimable->update($data);
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
