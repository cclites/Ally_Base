<?php

namespace App\Console\Commands;

use App\Address;
use App\Billing\Payer;
use App\BusinessChain;
use App\Caregiver;
use App\CaregiverLicense;
use App\Client;
use App\ExpirationType;
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

    protected function matchCaregiver(int $id, string $metaField = null): ?Caregiver
    {
        $query = Caregiver::forChains([$this->businessChain()->id]);

        if (empty($metaField)) {
            return $query->where('id', $id)->first();
        }

        return $qyery->whereMeta($metaField, $id)->first();
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
        $id = $this->resolve('Ally Caregiver ID', $row);
        $metaIdField = null;
        if (empty($id)) {
            $id = $this->resolve('Caregiver ID', $row);
            $metaIdField = 'Exported_ID';
        }
        if (! $caregiver = $this->matchCaregiver($id, $metaIdField)) {
            $this->warn("No matches for exported caregiver ID: $id");
            return false;
        }

        $date = filter_date($this->resolve('Expiration Date', $row));

        if (empty($date)) {
            $this->warn("No expiration date set on row $row");
            return false;
        }

        $license = new CaregiverLicense();
        $license->name = $this->resolve("Expiration Name", $row);
        $license->expires_at = $date;
        $license->description = $this->resolve('Notes', $row) ?? $this->resolve("Description", $row);

        if ($chainExpiration = $this->lookupChainExpiration($license->name)) {
            $license->chain_expiration_type_id = $chainExpiration->id;
        }

        return $caregiver->licenses()->save($license) ? $license : false;
    }

    private $chainExpirationTypes = null;
    private function lookupChainExpiration($name) {
        if (empty($this->chainExpirationTypes)) {
            $this->chainExpirationTypes = $this->businessChain()->expirationTypes
                ->map(function (ExpirationType $item) {
                    $item->type = strtolower($item->type);
                    return $item;
                });
        }

        return $this->chainExpirationTypes->where('type', strtolower($name))->first();
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function emptyRow(int $row)
    {
        $id = $this->resolve('Ally Caregiver ID', $row);
        if (empty($id)) {
            $id = $this->resolve('Caregiver ID', $row);
        }
        return empty($id);
    }
}
