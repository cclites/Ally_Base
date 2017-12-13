<?php

namespace App\Console\Commands;

use App\Imports\Worksheet;
use App\Imports\ShiftImporter;
use Illuminate\Console\Command;

class ImportShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shifts {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import shifts from a spreadsheet';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $worksheet = new Worksheet($this->argument('file'));
        $importer = new ShiftImporter($worksheet);
        $shifts = $importer->import();
        foreach($shifts as $shift) {
            $this->output->writeln('Created new shift ID ' . $shift->id);
        }
        $this->output->writeln('Summary: Created ' . count($shifts) . ' total shifts.');
    }
}
