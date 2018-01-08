<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Caregiver;
use App\Client;
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $business = Business::findOrFail($this->argument('business_id'));
        $this->output->writeln('Importing clients into ' . $business->name . ' in 5 seconds (Hit CTRL+C to cancel)..');
        sleep(5);

        $objPHPExcel = $this->loadSheet();

        $lastRow = $this->getRowCount($objPHPExcel);

        for($row=2; $row<$lastRow; $row++) {

            $name = $this->getValue($objPHPExcel, 'First Name', $row);
            if ($name) {
                $data['firstname'] = $this->getValue($objPHPExcel, 'First Name', $row);
                $data['lastname'] = $this->getValue($objPHPExcel, 'Last Name', $row);
                $this->output->writeln('Found client: ' . $data['firstname'] . ' ' . $data['lastname']);

                $data['ssn'] = str_pad($this->getValue($objPHPExcel, 'SSN', $row), 9, '0', STR_PAD_LEFT);
                $data['date_of_birth'] = filter_date($this->getValue($objPHPExcel, 'Date of Birth', $row));
                $data['client_type'] = $this->getValue($objPHPExcel, 'Client Type', $row);
                $data['password'] = bcrypt(str_random(12));
                $addressData['address1'] = $this->getValue($objPHPExcel, 'Address1', $row);
                $addressData['address2'] = $this->getValue($objPHPExcel, 'Address2', $row);
                $addressData['city'] = $this->getValue($objPHPExcel, 'City', $row);
                $addressData['state'] = $this->getValue($objPHPExcel, 'State', $row);
                $addressData['zip'] = $this->getValue($objPHPExcel, 'Zip', $row);
                $addressData['country'] = 'US';
                $addressData['type'] = 'evv';

                $phone1 = $this->getValue($objPHPExcel, 'Phone1', $row);
                $phone2 = $this->getValue($objPHPExcel, 'Phone2', $row);
                $email = trim($this->getValue($objPHPExcel, 'Email', $row));

                // Prevent Duplicates
                if ($email && User::where('email', $email)->exists()) {
                    continue;
                }

                // Resolve Client Type
                $data['client_type'] = $this->resolveClientType($data['client_type']);

                // Create client record
                // Fake username and email
                $data['username'] = 'placeholder' . uniqid();
                $data['email'] = 'placeholder' . uniqid();
                $data['business_id'] = $business->id;
                $client = new Client($data);
                if ($email) {
                    $client->email = $email;
                    $client->username = $email;
                }
                $client->save();
                if (!$email) {
                    $client->email = $client->id . '@noemail.allyms.com';
                    $client->username = $client->id . '@noemail.allyms.com';
                    $client->save();
                }

                // Create Address
                $address = new Address($addressData);
                $client->addresses()->save($address);

                // Create phone number(s)
                try {
                    if ($phone1) {
                        $phone = new PhoneNumber();
                        $phone->input($phone1);
                        $phone->type = 'primary';
                        $client->phoneNumbers()->save($phone);
                    }
                    if ($phone2) {
                        $phone = new PhoneNumber();
                        $phone->input($phone2);
                        $phone->type = 'home';
                        $client->phoneNumbers()->save($phone);
                    }
                }
                catch(\Exception $e) {

                }
            }

        }

    }

    protected function resolveClientType($string)
    {
        $string = trim($string);
        if (substr($string, 0, 3) === 'LTC') {
            return 'LTCI';
        }
        return $string;
    }
}
