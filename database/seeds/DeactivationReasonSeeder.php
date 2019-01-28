<?php

use Illuminate\Database\Seeder;
use App\Business;

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

        foreach(Business::all() as $business) {
            $clientReasons->each(function ($item) use ($business) {
                \App\DeactivationReason::create([
                    'name' => $item,
                    'type' => 'client',
                    'business_id' => $business->id,
                ]);
            });
    
            $caregiverReasons->each(function ($item) use ($business) {
                \App\DeactivationReason::create([
                    'name' => $item,
                    'type' => 'caregiver',
                    'business_id' => $business->id,
                ]);
            });
        }
    }
}
