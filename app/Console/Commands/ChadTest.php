<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChadTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:chad';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test chad.';

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
        /*
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

        $magicDays = $subArray;*/

        $magicDays = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23];

        $outputs = collect($magicDays)->map(function($magicNumber){


        });

            /*
            if($day % 12 == 0){
                $hour = 12;
            }else{
                $hour = $day % 12;
            }
            return [
                'day' => ucfirst( $this->convertToDay($day % 24 )),
                'hour' => $hour,
                'am_pm' => ($day % 24 >= 13) ? " PM" : " AM",
                'raw' => $day,
            ];
        })->toArray();

        foreach ($outputs as $output){
            echo $output['raw'] . " |-- " . $output['day'] . ", " . $output['hour'] . $output['am_pm'];
            echo "\n";
        }
        echo "\n";
            */

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

        echo "MATRIX:\n";

        foreach($daysHoursMatrix as $hours){
            echo json_encode($hours);
            echo "\n";
        }
        //echo json_encode($daysHoursMatrix);
        //echo "\n";
    }

    public function convertToDay($day){
        $days = ['sunday','monday','tuesday','wednesday','thursday','friday', 'saturday'];
        return $days[$day];
    }
}

/**
 * [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]
 * [24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47]
 * [48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71]
 * [72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95]
 * [96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119]
 * [120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143]
 * [144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167]
 */
