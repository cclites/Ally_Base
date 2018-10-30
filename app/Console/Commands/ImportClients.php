<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Client;
use App\EmergencyContact;
use App\User;

class ImportClients extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clients {--meta=:A comma separated list of fields to import into meta data} {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of clients into the system.';

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
        if ($this->business) return $this->business;
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
            'ssn' => $this->resolve('SSN', $row),
            'date_of_birth' => $this->resolve('Date of Birth', $row),
            'client_type' => $this->resolve('Client Type', $row) ?: 'private_pay',
            'username' => $this->resolve('Email', $row),
            'email' => $this->resolve('Email', $row),
            'client_type_descriptor' => $this->resolve('Client Type Descriptor', $row),
            'password' => bcrypt(str_random(12)),
            'active' => $this->resolve('Active', $row) ?? 1,
        ];

        // Prevent Duplicates
        if ($data['email'] && User::where('email', $data['email'])->exists()) {
            $this->output->writeln('Skipping duplicate email: ' . $data['email']);
            return false;
        }
        else if (!$data['email']) {
            $data['username'] = str_slug($data['firstname'] . $data['lastname'] . mt_rand(100,9999));
            $data['email'] = 'placeholder' . uniqid();
            $noemail = true;
        }

        /** @var Client $client */
        $client = $this->business()->clients()->create($data);
        if ($client) {
            // Replace placeholder email
            if (isset($noemail)) $client->setAutoEmail()->save();

            $this->importMeta($client, $row);
            $this->importAddresses($client, $row);
            $this->importPhoneNumbers($client, $row);
            $this->importEmergencyContacts($client, $row);
            $this->importNotes($client, $row);

            return $client;
        }

        return false;
    }

    /**
     * @param int $row
     * @param Client $client
     */
    protected function importAddresses(Client $client, int $row)
    {
        $addressData = [
            'address1' => $this->resolve('Address1', $row),
            'address2' => $this->resolve('Address2', $row),
            'city' => $this->resolve('City', $row),
            'state' => $this->resolve('State', $row),
            'zip' => $this->resolve('Zip', $row) ?: $this->resolve('PostalCode', $row),
            'country' => 'US',
            'type' => 'evv',
        ];
        $address = new Address($addressData);
        $client->addresses()->save($address);
    }

    /**
     * @param $client
     * @param int $row
     */
    protected function importEmergencyContacts($client, int $row)
    {
        for ($i = 1; $i <= 3; $i++) {
            if ($emergencyName = $this->resolve("Emerg. Contact #${i}: Name", $row)) {
                EmergencyContact::create([
                    'user_id' => $client->id,
                    'name' => $emergencyName,
                    'phone_number' => $this->resolve("Emerg. Contact #${i}: Phone", $row) ?? '',
                    'relationship' => $this->resolve("Emerg. Contact #${i}: Relationship", $row) ?? '',
                ]);
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
        return 'Importing clients into ' . $this->business()->name . '..';
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
     * Resolves the client type
     *
     * @param int $row
     * @param $cellValue
     * @return string
     */
    protected function resolveClientType(int $row, $cellValue)
    {
        if (ends_with($cellValue, ['LTC', 'LTCI'])) {
            return 'LTCI';
        }
        if (ends_with($cellValue, 'Private Pay')) {
            return 'private_pay';
        }
        return strtolower($cellValue);
    }


}
