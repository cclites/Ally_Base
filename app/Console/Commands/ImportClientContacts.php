<?php

namespace App\Console\Commands;

use App\Business;
use App\Client;
use App\ClientContact;

class ImportClientContacts extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:client-contacts {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of client contacts into the system for clients.';

    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * A store for duplicate row checks
     * @var array
     */
    protected $processedHashes = [];

    /**
     * A store for clients that already have contacts, db efficiency
     * @var array
     */
    protected $client_blacklist = [];
    protected $client; // to save db access since clients are consecutive


    public function __construct()
    {
        parent::__construct();
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

        // check if client already has contacts, skip if they do
        $client_id = intval( $this->resolve( 'Ally Client ID', $row ) );

        if( array_key_exists( $client_id, $this->client_blacklist ) ) return false;

        if( empty( $this->client ) || ( $this->client && $this->client->id !== $client_id ) ) {
            $this->client = Client::where('id', $client_id)
                ->where('business_id', $this->business->id)
                ->first();
            if (empty($this->client)) {
                return false;
            }
        }

        if( count( $this->client->contacts ) > 0 ){
            // if the client already has contacts, dont process..
            // also add their id to the blacklist to prevent wasting time grabbing this client from the db again

            $this->client_blacklist[ $client_id ] = 1;
            return false;
        }

        $is_payer = $this->resolve( 'Payer', $row ) == 'true' ? 1 : 0;
        $contact_email = $this->resolve( 'Email', $row );

        if( $is_payer ){
            // if is_payer == 1, replace client email address with contact email

            if( $contact_email ){

                $this->client->update([

                    'email' => $this->resolve( 'Email', $row ) ?? $this->client->user->email
                ]);
            }
        }

        $address = $this->resolve('Address', $row) . ' ' . $this->resolve('Address Line 2', $row);

        $data = [

            'client_id'           => $client_id,
            'name'                => $this->resolve('Contact Full Name', $row),
            'is_payer'            => $is_payer,
            'relationship'        => $this->resolve('Contact Type', $row),
            'relationship_custom' => $this->resolve('Relation Type', $row),
            'has_poa'             => $this->resolve('Power of Attorney', $row) == 'true' ? 1 : 0,
            'email'               => $contact_email,
            'phone1'              => $this->resolve('Mobile Phone', $row),
            'phone2'              => $this->resolve('Home Phone', $row),
            'address'             => $address,
            'city'                => $this->resolve('City', $row),
            'state'               => $this->resolve('State', $row),
            'zip'                 => $this->resolve('Postal Code', $row),
            'is_emergency'        => $this->resolve('Emergency', $row) == 'true' ? 1 : 0,
            'emergency_priority'  => $this->resolve('Primary', $row) == 'true' ? 1 : 2,
            'has_login_access'    => $this->resolve('Has Login Access', $row) == 'true' ? 1 : 0,
            'work_phone'          => $this->resolve('Work Phone', $row),
            'fax_number'          => $this->resolve('Fax Phone', $row),
        ];

        /** @var ClientComntact $clientContact */
        $clientContact = ClientContact::create( $data );
        if( $clientContact ) return $clientContact;

        return false;
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing client contacts into ' . $this->business()->name . '..';
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        return !$this->resolve('Ally Client ID', $row);
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
            $this->resolve('Ally Client ID', $row),
            $this->resolve('Contact Full Name', $row),
            $this->resolve('Primary', $row),
            $this->resolve('Payer', $row),
        ];

        $hash = md5(implode(',', array_filter($parts)));

        if (array_key_exists($hash, $this->processedHashes)) {
            return true;
        }

        $this->processedHashes[$hash] = 1;
        return false;
    }
}
