<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\User;
use Crypt;

class ImportCaregiversAdjustment extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:caretime-adjustment {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One=-off command to adjust an import for a CareTime caregiver list.';

    /**
     * A store for duplicate row checks
     * @var array
     */
    protected $processedHashes = [];

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

        $phone2 = $this->resolve( 'Phone2', $row );

        $data = [

            [ 'firstname'    , $this->resolve('First Name', $row) ],
            [ 'lastname'     , $this->resolve('Last Name', $row) ],
            // [ 'ssn'          , Crypt::encrypt( $this->resolve('SSN', $row) ) ],
            [ 'date_of_birth', $this->resolve('Date of Birth', $row) ],
            [ 'email'        , $this->resolve('Email', $row) ]
        ];

        dd( $data );

        // Find user
        $user = User::with( 'caregiver' )->where( $data )->get();

        dd( $user );

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
        return !$this->resolve('Last Name', $row);
    }

    /**
     * Check for duplicate data in the same import file
     *
     * @param int $row
     * @return bool
     * @throws \PHPExcel_Exception
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
        return true;
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
