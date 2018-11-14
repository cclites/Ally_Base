<?php

use Illuminate\Database\Seeder;

class DeactivationReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clientReasons = collect([
            'Deceased',
            'Temporary Hospital Stay',
            'Cancelled Service'
        ]);

        $caregiverReasons = collect([
            'Certification / License Expired',
            'Contractor does not want to work',
            'Contractor is not available'
        ]);

        $clientReasons->each(function ($item) {
            \App\DeactivationReason::create([
                'name' => $item,
                'type' => 'client'
            ]);
        });

        $caregiverReasons->each(function ($item) {
            \App\DeactivationReason::create([
                'name' => $item,
                'type' => 'caregiver'
            ]);
        });


    }
}
