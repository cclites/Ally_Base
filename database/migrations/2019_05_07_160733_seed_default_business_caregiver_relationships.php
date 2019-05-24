<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Caregiver;

class SeedDefaultBusinessCaregiverRelationships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('business_caregivers')->whereRaw(1)->delete();

        foreach (Caregiver::with('businessChains', 'businessChains.businesses')->get() as $caregiver) {
            foreach ($caregiver->businessChains as $chain) {
                $caregiver->businesses()->sync($chain->businesses->pluck('id'));
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('business_caregivers')->whereRaw(1)->delete();
    }
}
