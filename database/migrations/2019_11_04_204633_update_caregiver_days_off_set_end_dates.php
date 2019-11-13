<?php

use Illuminate\Database\Migrations\Migration;

class UpdateCaregiverDaysOffSetEndDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('caregiver_days_off')
            ->whereNull('end_date')
            ->orderBy('id')
            ->chunk(500, function ($daysOff) {
                foreach ($daysOff as $dayOff) {
                    \DB::table('caregiver_days_off')
                        ->where('id', '=', $dayOff->id)
                        ->update(['end_date' => $dayOff->start_date]);
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
        // No turning back
    }
}
