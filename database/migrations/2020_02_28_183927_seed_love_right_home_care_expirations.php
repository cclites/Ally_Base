<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedLoveRightHomeCareExpirations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('app.env') == 'testing') {
            return;
        }

        \DB::table('chain_expiration_types')->insert([
            ['type' => "Background check", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Business license", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "CA ID", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "CNA License", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "CPI CARD (Blue Card)", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "CPR Certification", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "California ID", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Car Insurance", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Car Registration", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Chest X-Ray", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "DMV ID CARD", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "DMV Report", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Dementia Care Training", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Driver's License", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Drug Test", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "E-verify", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "EDD Form Submit", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Employment Authorization card", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "First Aid Certification", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "HCA #", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "HHA Certification", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Identification Card", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Live Scan Clearance #", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "OTA", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Passport Card", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "RN: 715321", 'chain_id' => 83, 'created_at' => now()],
            ['type' => "Tuberculosis Test", 'chain_id' => 83, 'created_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
