<?php

use Illuminate\Database\Migrations\Migration;

class MigrateAdditionalClaimableServiceData extends Migration
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

        \App\Claims\ClaimableService::with(['shift'])
            ->chunk(400, function ($collection) {
                $collection->each(function (\App\Claims\ClaimableService $service) {
                    if (filled($service->shift)) {
                        if ($service->shift->hours_type != 'default') {
                            $service->is_overtime = true;
                        }
                    }

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
