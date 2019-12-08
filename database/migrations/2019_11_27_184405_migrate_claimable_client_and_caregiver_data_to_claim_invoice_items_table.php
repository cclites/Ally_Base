<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use App\Claims\ClaimInvoiceItem;

class MigrateClaimableClientAndCaregiverDataToClaimInvoiceItemsTable extends Migration
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

                if (
                    empty($item->claimable->caregiver_id) ||
                    empty($item->claimable->caregiver_first_name) ||
                    empty($item->claimable->caregiver_last_name)
                ) {
                    throw new InvalidArgumentException("Cannot convert Claim #{$item->claim->id} because of blank caregiver info.");
                }

                $data = [
                    // Move client data off the claim invoice
                    'client_id' => $item->claim->client_id,
                    'client_first_name' => $item->claim->client_first_name,
                    'client_last_name' => $item->claim->client_last_name,
                    'client_dob' => $item->claim->client_dob,
                    'client_medicaid_id' => $item->claim->client_medicaid_id,
                    'client_medicaid_diagnosis_codes' => $item->claim->client_medicaid_diagnosis_codes,

                    // Move caregiver data from the claimable item (if it exists)
                    'caregiver_id' => $item->claimable->caregiver_id,
                    'caregiver_first_name' => $item->claimable->caregiver_first_name,
                    'caregiver_last_name' => $item->claimable->caregiver_last_name,
                    'caregiver_gender' => $item->claimable->caregiver_gender,
                    'caregiver_dob' => $item->claimable->caregiver_dob,
                    'caregiver_ssn' => $item->claimable->caregiver_ssn,
                    'caregiver_medicaid_id' => $item->claimable->caregiver_medicaid_id,
                ];

                $item->update($data);
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
