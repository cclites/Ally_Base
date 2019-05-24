<?php

namespace App\Console\Commands;

use App\Address;
use App\BusinessChain;
use App\Caregiver;
use App\CaregiverRestriction;
use App\StatusAlias;
use App\User;

class ImportCaregivers extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:caregivers {--meta=:A comma separated list of fields to import into meta data} {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of caregivers into the system.';

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

        $statusAlias = $this->resolveStatusAlias($row);

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
            'active' => $statusAlias ? $statusAlias->active : $this->resolve('Active', $row),
            'preferences' => $this->resolve('Preferences', $row),
            'password' => bcrypt(str_random(12)),
            'status_alias_id' => $statusAlias->id ?? null,
        ];

        // Prevent Duplicates
        if ($data['email'] && User::where('email', $data['email'])->exists()) {
            $this->output->writeln('Skipping duplicate email: ' . $data['email']);
            return false;
        } else if (!$data['email']) {
            $data['username'] = str_slug($data['firstname'] . $data['lastname'] . mt_rand(100, 9999));
            $data['email'] = 'placeholder' . uniqid();
            $noemail = true;
        }


        /** @var Caregiver $caregiver */
        $caregiver = $this->businessChain()->caregivers()->create($data);
        if ($caregiver) {
            // Assign to all the chain's businesses.
            $caregiver->ensureBusinessRelationships($this->businessChain());

            // Replace placeholder email
            if (isset($noemail)) {
                $caregiver->setAutoEmail()->save();
            }

            $this->importAvailability($caregiver, $row);
            $this->importMeta($caregiver, $row);
            $this->importAddresses($caregiver, $row);
            $this->importPhoneNumbers($caregiver, $row);
            $this->importNotes($caregiver, $row);
            $this->importRestrictions($caregiver, $row);

            return $caregiver;
        }

        return false;
    }

    protected function importAvailability(Caregiver $caregiver, int $row)
    {
        $availabilityData = [
            'monday' => $this->resolveBoolean('Mondays', $row, null),
            'tuesday' => $this->resolveBoolean('Tuesdays', $row, null),
            'wednesday' => $this->resolveBoolean('Wednesdays', $row, null),
            'thursday' => $this->resolveBoolean('Thursdays', $row, null),
            'friday' => $this->resolveBoolean('Fridays', $row, null),
            'saturday' => $this->resolveBoolean('Saturdays', $row, null),
            'sunday' => $this->resolveBoolean('Sundays', $row, null),
            'morning' => $this->resolveBoolean('Mornings', $row, null),
            'afternoon' => $this->resolveBoolean('Afternoons', $row, null),
            'evening' => $this->resolveBoolean('Evenings', $row, null),
            'night' => $this->resolveBoolean('Overnight', $row, null),
            'live_in' => $this->resolveBoolean('Live In', $row, null),
        ];

        $caregiver->setAvailability(array_filter($availabilityData, 'is_bool'));
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


    protected function importRestrictions(Caregiver $caregiver, int $row)
    {
        if ($restrictionText = $this->resolve('Restrictions', $row)) {
            foreach(explode("\n\n", $restrictionText) as $description) {
                $restriction = new CaregiverRestriction(['description' => str_limit($description, 253, '..')]);
                $caregiver->restrictions()->save($restriction);
            }
        }
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing caregivers into ' . $this->businessChain()->name . '..';
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

    /**
     * Resolve the Hire Date in YYYY-MM-DD or MM/DD/YYYY formats.
     *
     * @param int $row
     * @param $cellValue
     * @return null|string
     */
    protected function resolveHireDate(int $row, $cellValue)
    {
        return $this->transformDateValue($cellValue);
    }

    /**
     * Resolve the status alias from the 'Status' column
     *
     * @param int $row
     * @return StatusAlias|null
     * @throws \PHPExcel_Exception
     */
    protected function resolveStatusAlias(int $row): ?StatusAlias
    {
        if ($name = $this->resolve('Status', $row)) {
            $status = StatusAlias::where('name', $name)
                ->where('chain_id', $this->businessChain()->id)
                ->where('type', 'caregiver')
                ->first();

            return $status;
        }

        return null;
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

}
