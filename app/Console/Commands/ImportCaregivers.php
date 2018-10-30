<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Caregiver;
use App\User;

class ImportCaregivers extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:caregivers {--meta=:A comma separated list of fields to import into meta data} {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of caregivers into the system.';

    /**
     * @var \App\Business
     */
    protected $business;


    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\Business
     */
    protected function business()
    {
        if ($this->business) {
            return $this->business;
        }
        return $this->business = Business::findOrFail($this->argument('business_id'));
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
        $data = [
            'firstname' => $this->resolve('First Name', $row),
            'lastname' => $this->resolve('Last Name', $row),
            'title' => $this->resolve('Classification', $row),
            'ssn' => $this->resolve('SSN', $row),
            'date_of_birth' => $this->resolve('Date of Birth', $row),
            'username' => $this->resolve('Email', $row),
            'email' => $this->resolve('Email', $row),
            'hire_date' => $this->resolve('Hire Date', $row),
            'gender' => $this->resolve('Gender', $row),
            'active' => $this->resolve('Active', $row) ?? 1,
            'password' => bcrypt(str_random(12)),
        ];

        // Prevent Duplicates
        if ($data['email'] && User::where('email', $data['email'])->exists()) {
            $this->output->writeln('Skipping duplicate email: ' . $data['email']);
            return false;
        } else {
            if (!$data['email']) {
                $data['username'] = str_slug($data['firstname'] . $data['lastname'] . mt_rand(100, 9999));
                $data['email'] = 'placeholder' . uniqid();
                $noemail = true;
            }
        }

        /** @var Caregiver $caregiver */
        $caregiver = $this->business()->caregivers()->create($data);
        if ($caregiver) {
            // Replace placeholder email
            if (isset($noemail)) {
                $caregiver->setAutoEmail()->save();
            }

            $this->importMeta($caregiver, $row);
            $this->importAddresses($caregiver, $row);
            $this->importPhoneNumbers($caregiver, $row);
            $this->importNotes($caregiver, $row);

            return $caregiver;
        }

        return false;
    }

    /**
     * @param int $row
     * @param Caregiver $caregiver
     */
    protected function importAddresses(Caregiver $caregiver, int $row)
    {
        $addressData = [
            'address1' => $this->resolve('Address1', $row),
            'address2' => $this->resolve('Address2', $row),
            'city' => $this->resolve('City', $row),
            'state' => $this->resolve('State', $row),
            'zip' => $this->resolve('Zip', $row) ?: $this->resolve('PostalCode', $row),
            'country' => 'US',
            'type' => 'home',
        ];
        $address = new Address($addressData);
        $caregiver->addresses()->save($address);
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing caregivers into ' . $this->business()->name . '..';
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
     * Resolves the Classification (Title), transform full licenses to abbreviations
     *
     * @param int $row
     * @param $cellValue
     * @return string
     */
    protected function resolveClassification(int $row, $cellValue)
    {
        if (starts_with($cellValue, 'Home Health')) {
            return 'HHA';
        }
        if (starts_with($cellValue, 'Certified Nurs')) {
            return 'CNA';
        }
        if (strlen($cellValue) > 4) {
            // too long to be a normal title, ignore
            return '';
        }
        return strtoupper($cellValue);
    }



}
