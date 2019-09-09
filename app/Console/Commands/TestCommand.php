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
        if (config('app.env') == 'production') {
            return;
        }

        $shiftStartDay = \Carbon::today();
        $shiftEndDay = \Carbon::today()->addDay();

        echo $shiftStartDay . "\n";
        echo $shiftEndDay . "\n";

        $vacStartDay = \Carbon::today()->today()->addDay();
        $vacEndDay = \Carbon::today()->addDays(4);

        echo $vacStartDay . "\n";
        echo $vacEndDay . "\n";


       // if( ($vacStartDay < $shiftEndDay && $vacEndDay > $shiftStartDay) ){
        if( $shiftEndDay->lessThanOrEqualTo($vacStartDay) && $shiftStartDay->greaterThanOrEqualTo($vacEndDay) )
        {
            echo "Shifts overlap\n";
        }else{
            echo "Shifts are in the clear\n";
        }

    }
}
