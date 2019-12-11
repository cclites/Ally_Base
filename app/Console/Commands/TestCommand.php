<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use mikehaertl\pdftk\Pdf;

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
        $pdf = new Pdf('resources/pdf_forms/caregiver1099s/2019/f1099msc_19.pdf');
        $pdf->cat([7]) //individual pages
        ->saveAs('resources/pdf_forms/caregiver1099s/2019/CopyC_1099msc.pdf');
    }
}
