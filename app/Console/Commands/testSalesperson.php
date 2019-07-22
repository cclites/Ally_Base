<?php

namespace App\Console\Commands;

use App\SalesPerson;
use Illuminate\Console\Command;
use App\Client;

use Illuminate\Database\Eloquent\Model;
use DB;

use Log;

class testSalesperson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test_salesperson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

       $s =  SalesPerson::query()->where('business_id', 57)
                            ->get()
                            ->map(function(SalesPerson $salesperson){

                                echo json_encode($salesperson->clientIds()) . "\n";


                            })->values();


    }
}
