<?php

namespace App\Console\Commands;

use App\Business;
use App\BusinessChain;
use App\ReferralSource;

class ImportReferrals extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:referrals {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel sheet of referrals.';

    /**
     * @var \App\BusinessChain
     */
    protected $chain;

    /**
     * A store for duplicate row checks
     * @var array
     */
    protected $processedHashes = [];

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
     * Return the current business chain model for who the data should be imported in to
     *
     * @return \App\BusinessChain
     */
    protected function chain()
    {
        if ($this->chain) return $this->chain;
        return $this->chain = BusinessChain::findOrFail($this->argument('chain_id'));
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
        if ($this->duplicateDataProcessed($row)) {
            $this->output->writeln('Skipping duplicate data found on row : ' . $row);
            return false;
        }

        $first = $this->resolve('First Name', $row);
        $last = $this->resolve('Last Name', $row);
        $full_name = ( empty( $first ) && empty( $last ) ? 'Default' : $first . ' ' . $last );

        $organization_name = $this->resolve('Company', $row) ?? $full_name;

        $data = [
            'type'         => 'client', // or caregiver?
            'chain_id'     => $this->chain()->id,
            'organization' => $organization_name,
            'contact_name' => $full_name,
            'phone'        => $this->resolve('Home Phone', $row),
            'is_company'   => $this->resolve('Is Company?', $row) == 'true' ? 1 : 0,
            'source_owner' => $this->resolve('Referral Source Owner', $row),
            'source_type'  => $this->resolve('Referral Source Type', $row),
            'web_address'  => $this->resolve('Web Address', $row),
            'work_phone'   => $this->resolve('Work Phone', $row)
        ];

        $referral = ReferralSource::create($data);
        if ($referral) {

            return $referral;
        }

        return false;
    }

    /**
     * Check for duplicate data in the same import file
     *
     * @param int $row
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function duplicateDataProcessed(int $row)
    {
        $parts = [
            $this->resolve('Full Name', $row),
            $this->resolve('Company', $row),
            $this->resolve('Is Company?', $row)
        ];

        $hash = md5(implode(',', array_filter($parts)));

        if (array_key_exists($hash, $this->processedHashes)) {
            return true;
        }

        $this->processedHashes[$hash] = 1;
        return false;
    }


    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing referrals into ' . $this->chain()->name . '..';
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
        return !$this->resolve('Full Name', $row);
    }

    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\Business
     */
    protected function business()
    {
        // ReferralSource belong to the BusinessChain, not the Business.
        return null;
    }
}
