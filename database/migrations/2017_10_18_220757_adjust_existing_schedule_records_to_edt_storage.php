<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustExistingScheduleRecordsToEdtStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') === 'production') {
            $schedules = \App\Schedule::all();
            $count = 0;
            foreach($schedules as $schedule) {
                $start = new \Carbon\Carbon($schedule->start_date . ' ' . $schedule->time, 'UTC');
                $start->subHours(4);

                if ($schedule->end_date != \App\Schedule::FOREVER_ENDDATE) {
                    $end = new \Carbon\Carbon($schedule->end_date . ' ' . $schedule->time, 'UTC');
                    $end->subHours(4);
                    $schedule->end_date = $end->format('Y-m-d');
                }

                $schedule->start_date = $start->format('Y-m-d');
                $schedule->time = $start->format('H:i:s');
                if ($schedule->save()) $count++;
            }
            echo "$count schedules updated.";
        }
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
