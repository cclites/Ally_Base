<?php

namespace App\Console\Commands;

use App\Actions\CreateClient;
use App\Address;
use App\Billing\ClientPayer;
use App\Billing\Payer;
use App\Business;
use App\Client;
use App\EmergencyContact;
use App\StatusAlias;
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
     * @var \App\Actions\CreateClient
     */
    protected $createClient;

    /**
     * A store for duplicate row checks
     * @var array
     */
    protected $processedHashes = [];


    public function __construct(CreateClient $createClient)
    {
        parent::__construct();
        $this->createClient = $createClient;
    }


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
        if ($this->duplicateDataProcessed($row)) {
            $this->output->writeln('Skipping duplicate data found on row : ' . $row);
            return false;
        }

        $statusAlias = $this->resolveStatusAlias($row);

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
            'active' => $statusAlias ? $statusAlias->active : $this->resolve('Active', $row),
            'business_id' => $this->business()->id,
            'hic' => $this->resolve('HIC', $row),
            'status_alias_id' => $statusAlias->id ?? null,
        ];

        // Prevent Duplicates
        if ($data['email'] && User::where('email', $data['email'])->exists()) {
            $this->output->writeln('Skipping duplicate email: ' . $data['email']);
            return false;
        }
        else if (!$data['email']) {
            $data['username'] = str_slug($data['firstname'] . $data['lastname'] . mt_rand(100,9999));

        }

        /** @var Client $client */
        $client = $this->createClient->create($data);
        if ($client) {

            $this->importMeta($client, $row);
            $this->importAddresses($client, $row);
            $this->importPhoneNumbers($client, $row);
            $this->importEmergencyContacts($client, $row);
            $this->importNotes($client, $row);
            $this->importPayer($client, $row);

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
    protected function importEmergencyContacts(Client $client, int $row)
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

    protected function importPayer(Client $client, int $row)
    {
        if (!$payerName = $this->resolve('Payer', $row)) {
            return;
        }

        $payer = Payer::where('chain_id', $this->business()->chain_id)
            ->where('name', $payerName)
            ->first();

        if ($payer) {
            $clientPayer = new ClientPayer([
                'payer_id' => $payer->id,
                'policy_number' => null,
                'effective_start' => date('Y') . '-01-01',
                'effective_end' => '9999-12-31',
                'payment_allocation' => ClientPayer::ALLOCATION_BALANCE,
            ]);

            if ($client->payers()->save($clientPayer)) {
                // Delete default payer (created by CreateDefaultClientPayer event listener)
                ClientPayer::where('client_id', $client->id)
                    ->where('payer_id', '!=', $payer->id)
                    ->delete();
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
                ->where('chain_id', $this->business()->chain_id)
                ->where('type', 'client')
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
