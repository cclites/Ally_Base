<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChadTest3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:chad3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert Days and Times to magic numbers.';

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
     * This is what would come in from Care Match
     *
     * @return mixed
     */
    public function handle()
    {
        $days = ['tuesday', 'saturday'];
        $shift_start_time = '08:00';
        $shift_end_time = '14:00';

        //I don't need to worry about consecutive times, just start time and end time (for now)

        $shiftStart = floatval($shift_start_time);
        $shiftEnd = floatVal($shift_end_time);

        echo "$shiftStart\n";
        echo "$shiftEnd\n";


        return;
    }

    public function getStartDayAsInt($day){
        return floor($day / 24);
    }

    public function setHourAs12($day){
        return ($day % (12)) + 1;
    }

    public function getAmPmLabel($day){
       return $day % 24 > 12 ? " PM" : " AM";
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
