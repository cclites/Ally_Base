<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PopulateTellusVisitEditReasonCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('visit_edit_reasons')->insert([
            'code' => '500',
            'description' => 'In-Home Respite Services',
        ]);

        \DB::table('visit_edit_reasons')->insert([
            'code' => '505',
            'description' => 'Consumer Directed Services (CDS) Employer Time Correction',
        ]);

        \DB::table('visit_edit_reasons')->insert([
            'code' => '600',
            'description' => 'Service Suspension',
        ]);

        \DB::table('visit_edit_reasons')->insert([
            'code' => '700',
            'description' => 'Downward Adjustment to Billed Hours',
        ]);

        \DB::table('visit_edit_reasons')->insert([
            'code' => '701',
            'description' => 'Upward Adjustment to Billed Hours',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('visit_edit_reasons')->whereIn('code', [
            '701', '700', '600', '505', '500',
        ])->delete();
    }
}
