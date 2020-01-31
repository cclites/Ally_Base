<?php

namespace App\Console\Commands;

use App\BusinessChain;
use App\User;
use App\PhoneNumber;

class OnceImportCaregiversAdjustment extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:import-caretime-adjustment {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command to adjust an import for a CareTime caregiver list.';

    /**
     * @var \App\BusinessChain
     */
    protected $businessChain;

    /**
     * A store for duplicate row checks
     * @var array
     */
    protected $processedHashes = [];

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

        $chain = $this->businessChain();

        $data = [
            ['firstname', $this->resolve('First Name', $row)],
            ['lastname', $this->resolve('Last Name', $row)],
            ['email', $this->resolve('Email', $row)],
        ];

        // Find user
        $user = User::where($data)->whereHas('caregiver', function ($q) use ($chain) {
            $q->forChains([$chain->id]);
        })->get();

        if (count($user) == 1) {
            try {
                $user = $user->first();

                $phone2 = $this->resolve('Phone2', $row);
                $number = preg_replace('/[^\d\-]/', '', $phone2);
                if (empty($number)) {
                    return true;
                }
                
                $phone = PhoneNumber::fromInput('mobile', $number);
                $phone->number(); // This should throw an exception if invalid format
                $phone->receives_sms = 1;
                $user->role->phoneNumbers()->save($phone);

                return true;
            } catch (\Exception $ex) {
                $this->output->writeln('Failed to update row ' . $row . ' with exception ' . $ex->getMessage());
                return false;
            }
        }

        $this->output->writeln('Failed to update row ' . $row);

        return false;
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        return ! $this->resolve('Last Name', $row);
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
            $this->resolve('Name', $row),
            $this->resolve('First Name', $row),
            $this->resolve('Last Name', $row),
            $this->resolve('Email', $row),
            $this->resolve('Address1', $row),
        ];

        $hash = md5(implode(',', array_filter($parts)));

        if (array_key_exists($hash, $this->processedHashes)) {
            return true;
        }

        $this->processedHashes[$hash] = 1;
        return false;
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

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Fixing caregiver phone numbers..';
    }
}
