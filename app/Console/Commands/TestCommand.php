<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:stuff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test stuff.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $daysInWeekLimit = 7;
        $hoursPerDayLimit = 24;
        $daysHoursMatrix = [];

        /**
         * Tests for creating and manipulating magicDays
         */

        //$i represents days of the week, starting with 0=sunday.
        for($i = 0; $i<$daysInWeekLimit; $i += 1){

            $baseForWeek = $i * $hoursPerDayLimit;
            $daysMatrix = [];

            //$j represents the hour of the day in 24hr format
            for($j=0; $j<$hoursPerDayLimit; $j+= 1){
                $daysMatrix[] = $baseForWeek + $j;
            }
            $daysHoursMatrix[] = $daysMatrix;
        }

        $daysHoursCombined = array_merge(...$daysHoursMatrix);
        $subArray = array_rand($daysHoursCombined, 8);

        $magicDays = $subArray;

        $outputs = collect($magicDays)->map(function($day){

            if($day % 24 == 0 || $day % 12 == 0){
                $hour = 12;
            }else{
                $hour = $day % 12;
            }
            return [
                'day'=>ucfirst( $this->convertToDay($day % 7) ),
                'hour'=> $hour,
                'am_pm'=> ($day % 24 >= 13) ? " PM" : " AM",
            ];
        })->toArray();

        foreach ($outputs as $output){
            echo $output['day'] . ", " . $output['hour'] . $output['am_pm'];
            echo "\n";
        }
        echo "\n";

        /*
        foreach ($magicDays as $day){

            $modDayOfWeek = $day % 7;

            echo "Day of week is $modDayOfWeek\n";

            $amPm = $day % 24 > 12  ? " PM" : " AM";

            echo "AM/PM is $amPm\n";

            $modHourOfDay = $day % 12;

            if($modHourOfDay == 0){
                $modHourOfDay = 1;
            }

            echo "Mod hour of day is $modHourOfDay\n";

            $formattedTime = $modHourOfDay . $amPm;

            echo $this->convertToDay($modDayOfWeek) . ", $formattedTime\n";
            /*
            if($hourOfDayAsInt == 0){
                echo "================================Adding 1 to hourOfDayAsInt\n";
                $hourOfDayAsInt += 1;
            }else{
                echo "hourOfDayAsInt stands\n";
            }

            echo "Hour of day as int: $hourOfDayAsInt\n";

            $formattedStartHour = ($hourOfDayAsInt >= 13) ? ($hourOfDayAsInt - 12) . " AM" : " PM";

            echo "Formatted start Hour Of Day: $formattedStartHour\n";

            //generate end time by simply adding an hour
            if($hourOfDayAsInt + 1 > 23){
                $hourOfDayAsInt = 0;
            }else{
                $hourOfDayAsInt += 1;
            }*/

            //echo "Hour of day as int:  $hourOfDayAsInt\n";

            //$endHourOfDay = ($hourOfDayAsInt + 1);
            //$endHourOfDay = $endHourOfDay > 12 ? $endHourOfDay - 11 . " AM" : " PM";

            //echo $this->convertToDay($dayOfWeek) . "   $startHourOfDay  $endHourOfDay\n";



        //}

        //TODO: Convert day of week to a text representation

        //echo "MATRIX:\n";
        //echo json_encode($daysHoursMatrix);
        //echo "\n";
    }

    public function convertToDay($day){
        $days = ['sunday','monday','tuesday','wednesday','thursday','friday', 'saturday'];
        return $days[$day];
    }
}
