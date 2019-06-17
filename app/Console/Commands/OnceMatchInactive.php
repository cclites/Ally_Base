<?php

namespace App\Console\Commands;

use App\BusinessChain;
use App\Caregiver;

class OnceMatchInactive extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:match_inactive {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One time command to fix VIP "Inactive" statuses';

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
     * Import the specified row of data from the sheet and return the related model
     *
     * @param int $row
     * @return \Illuminate\Database\Eloquent\Model|false
     * @throws \Exception
     */
    protected function importRow(int $row)
    {
        $id = $this->resolve('ID', $row);
        if (!$caregiver = $this->matchCaregiver($id)) {
            $this->output->writeln("No matches for exported caregiver ID $id");
            return false;
        }

        $caregiver->update(['active' => 0]);
        return $caregiver;
    }


    protected function matchCaregiver(int $exportedId): ?Caregiver
    {
        return Caregiver::forChains([$this->businessChain()->id])
            ->whereMeta('Exported_ID', $exportedId)
            ->first();
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        return !$this->resolve('Last Name', $row);
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Run a one-time command to fix VIP inactive caregivers';
    }

    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\Business
     */
    protected function business()
    {
        return null;
    }

    private function businessChain()
    {
        return BusinessChain::find(51); // Hardcoded to VIP
    }
}
