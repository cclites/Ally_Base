<?php

namespace App\Console\Commands;

use App\Address;
use App\Billing\Payer;
use App\BusinessChain;
use App\Caregiver;
use App\CaregiverLicense;
use App\Client;
use App\PhoneNumber;

class ImportExpirations extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:expirations {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of Caregiver License Expirations';

    /**
     * @var \App\BusinessChain
     */
    protected $businessChain;


    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\BusinessChain
     */
    protected function businessChain()
    {
        if ($this->businessChain) {
            return $this->businessChain;
        }
        return $this->businessChain = BusinessChain::findOrFail($this->argument('chain_id'));
    }

    /**
     * Return the current business model for who the data should be imported in to
     * NOTE: Business Chain should be used for caregivers.  This is only for compatibility with business-only resources.
     *
     * @return \App\Business
     */
    protected function business()
    {
        return $this->businessChain()->businesses->first();
    }

    protected function matchCaregiver(int $exportedId): ?Caregiver
    {
        return Caregiver::forChains([$this->businessChain()->id])
            ->whereMeta('Exported_ID', $exportedId)
            ->first();
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
        $id = $this->resolve('Caregiver ID', $row);
        if (!$caregiver = $this->matchCaregiver($id)) {
            $this->output->writeln("No matches for exported caregiver ID $id");
            return false;
        }

        $license = new CaregiverLicense();
        $license->name = $this->resolve("Expiration Name", $row);
        $license->expires_at = filter_date($this->resolve('Expiration Date', $row)) ?: '9999-12-31';
        $license->description = $this->resolve('Notes', $row) ?? $this->resolve("Description", $row);

        return $caregiver->licenses()->save($license) ? $license : false;
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing expirations into ' . $this->businessChain()->name . '..';
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        return !$this->resolve('Expiration Name', $row);
    }
}
