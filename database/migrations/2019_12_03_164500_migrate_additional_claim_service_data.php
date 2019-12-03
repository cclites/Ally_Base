<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migrate all the data that is now missing on Claimable objects
 * because we added new fields.
 *
 * Class MigrateAdditionalClaimServiceData
 */
class MigrateAdditionalClaimServiceData extends Migration
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

        \App\Claims\ClaimableService::with(['client', 'shift', 'client.caseManager'])
            ->whereHas('client')
            ->chunk(400, function ($collection) {
                $collection->each(function (\App\Claims\ClaimableService $service) {
                    // Note: client_program_number and client_cirts_number do not need to be migrated here
                    // because those client fields are created after this migration

                    if (filled($service->shift)) {
                        if ($service->shift->hours_type != 'default') {
                            $service->is_overtime = true;
                        }
                    }

                    $payer = $service->client->payers->where('payer_id', $service->payer_id);
                    $caregiverSignature = \DB::table('signatures')
                        ->where('signable_id', $service->shift_id)
                        ->where('signable_type', 'shifts')
                        ->where('meta_type', 'caregiver')
                        ->select('id')
                        ->first();

                    if (filled($caregiverSignature)) {
                        $service->caregiver_signature_id = $caregiverSignature->id;
                    }

                    $clientSignature = \DB::table('signatures')
                        ->where('signable_id', $service->shift_id)
                        ->where('signable_type', 'shifts')
                        ->where('meta_type', 'client')
                        ->select('id')
                        ->first();

                    if (filled($clientSignature)) {
                        $service->client_signature_id = $clientSignature->id;
                    }

                    $service->client_case_manager = optional($service->client->caseManager)->name_last_first;
                    $service->client_ltci_policy_number = $service->client->getPolicyNumber();
                    $service->client_ltci_claim_number = $service->client->getClaimNumber();
                    $service->save();
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
