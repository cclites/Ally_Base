<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Client;
use App\EmergencyContact;
use App\Note;
use App\PhoneNumber;
use App\User;

class ImportClients extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clients {business_id} {file}';

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
            'client_type' => $this->resolve('Client Type', $row),
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
            $data['username'] = $data['firstname'] . $data['lastname'] . mt_rand(100,9999);
            $data['email'] = 'placeholder' . uniqid();
            $noemail = true;
        }

        /** @var Client $client */
        $client = $this->business()->clients()->create($data);
        if ($client) {
            // Replace placeholder email
            if (isset($noemail)) $client->setAutoEmail()->save();

            // Create Address
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

            // Create Phone Numbers
            try {
                $phoneFields = ['primary' => 'Phone1', 'home' => 'Phone2'];
                foreach ($phoneFields as $type => $phoneField) {
                    $number = preg_replace('/[^\d\-]/', '', $this->resolve($phoneField, $row));
                    $phone = new PhoneNumber(['type' => $type]);
                    $phone->input($number);
                    $client->phoneNumbers()->save($phone);
                }
            }
            catch (\Exception $e) {}

            // Create Emergency Contacts
            for($i = 1; $i <= 3; $i++) {
                if ($emergencyName = $this->resolve("Emerg. Contact #${i}: Name", $row)) {
                    EmergencyContact::create([
                        'user_id' => $client->id,
                        'name' => $emergencyName,
                        'phone_number' => $this->resolve("Emerg. Contact #${i}: Phone", $row) ?? '',
                        'relationship' => $this->resolve("Emerg. Contact #${i}: Relationship", $row) ?? '',
                    ]);
                }
            }

            // Create Note
            if ($officeNote = $this->resolve("OfficeNote", $row)) {
                $officeUser = $this->business()->users()->first();
                $client->notes()->save(new Note([
                    'body' => $officeNote . "\n\nImported on " . date('F j, Y'),
                    'created_by' => $officeUser->id,
                    'business_id' => $this->business()->id,
                ]));
            }

            return $client;
        }

        return false;
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
     * Allows values like: 0, 1, 'I', 'A', 'FALSE', 'TRUE'
     *
     * @param int $row
     * @param $cellValue
     * @return int|null
     */
    protected function resolveActive(int $row, $cellValue)
    {
        if (is_numeric($cellValue)) return (int) $cellValue;

        if (strlen($cellValue)) {
            $validStrings = ['A', 'TRUE'];
            if (in_array(strtoupper($cellValue), $validStrings)) {
                return 1;
            }
            return 0;
        }

        return null;
    }

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

    protected function resolveEmail(int $row, $cellValue) {
        if (filter_var($cellValue, FILTER_VALIDATE_EMAIL)) {
            return $cellValue;
        }
        return null;
    }

    protected function resolveFirstName(int $row, $cellValue)
    {
        if ($cellValue) return $cellValue;

        if ($name = $this->getNameArray($row)) {
            return $name['first'];
        }
    }

    protected function resolveLastName(int $row, $cellValue)
    {
        if ($cellValue) return $cellValue;

        if ($name = $this->getNameArray($row)) {
            return $name['last'];
        }
    }

    protected function getNameArray(int $row)
    {
        if ($name = $this->resolve('Name', $row)) {
            if (strpos($name, ',') !== false) {
                // Last, First format
                $name = explode(',', $name);
                return [
                    'first' => trim($name[1] ?? ''),
                    'last' => trim($name[0] ?? ''),
                ];
            }
            else {
                // First Last format, put potential middle name with first
                $name = explode(' ', $name);
                $last = array_pop($name);
                return [
                    'first' => implode(' ', $name),
                    'last' => $last,
                ];
            }
        }

        return false;
    }

    protected function resolveState(int $row, $cellValue)
    {
        if (strlen($cellValue) > 2) {
            $states = [
                'AL'=>'ALABAMA',
                'AK'=>'ALASKA',
                'AS'=>'AMERICAN SAMOA',
                'AZ'=>'ARIZONA',
                'AR'=>'ARKANSAS',
                'CA'=>'CALIFORNIA',
                'CO'=>'COLORADO',
                'CT'=>'CONNECTICUT',
                'DE'=>'DELAWARE',
                'DC'=>'DISTRICT OF COLUMBIA',
                'FM'=>'FEDERATED STATES OF MICRONESIA',
                'FL'=>'FLORIDA',
                'GA'=>'GEORGIA',
                'GU'=>'GUAM GU',
                'HI'=>'HAWAII',
                'ID'=>'IDAHO',
                'IL'=>'ILLINOIS',
                'IN'=>'INDIANA',
                'IA'=>'IOWA',
                'KS'=>'KANSAS',
                'KY'=>'KENTUCKY',
                'LA'=>'LOUISIANA',
                'ME'=>'MAINE',
                'MH'=>'MARSHALL ISLANDS',
                'MD'=>'MARYLAND',
                'MA'=>'MASSACHUSETTS',
                'MI'=>'MICHIGAN',
                'MN'=>'MINNESOTA',
                'MS'=>'MISSISSIPPI',
                'MO'=>'MISSOURI',
                'MT'=>'MONTANA',
                'NE'=>'NEBRASKA',
                'NV'=>'NEVADA',
                'NH'=>'NEW HAMPSHIRE',
                'NJ'=>'NEW JERSEY',
                'NM'=>'NEW MEXICO',
                'NY'=>'NEW YORK',
                'NC'=>'NORTH CAROLINA',
                'ND'=>'NORTH DAKOTA',
                'MP'=>'NORTHERN MARIANA ISLANDS',
                'OH'=>'OHIO',
                'OK'=>'OKLAHOMA',
                'OR'=>'OREGON',
                'PW'=>'PALAU',
                'PA'=>'PENNSYLVANIA',
                'PR'=>'PUERTO RICO',
                'RI'=>'RHODE ISLAND',
                'SC'=>'SOUTH CAROLINA',
                'SD'=>'SOUTH DAKOTA',
                'TN'=>'TENNESSEE',
                'TX'=>'TEXAS',
                'UT'=>'UTAH',
                'VT'=>'VERMONT',
                'VI'=>'VIRGIN ISLANDS',
                'VA'=>'VIRGINIA',
                'WA'=>'WASHINGTON',
                'WV'=>'WEST VIRGINIA',
                'WI'=>'WISCONSIN',
                'WY'=>'WYOMING',
                'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
                'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
                'AP'=>'ARMED FORCES PACIFIC'
            ];

            return (string) array_search(strtoupper($cellValue), $states);
        }

        return strtoupper($cellValue);
    }
}
