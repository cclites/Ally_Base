<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Caregiver;
use App\PhoneNumber;
use App\User;

class ImportGenerationsCaregivers extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:generations_caregivers {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a "Generations" excel export of caregivers into the system.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $business = Business::findOrFail($this->argument('business_id'));
        $this->output->writeln('Importing caregivers into ' . $business->name . ' in 5 seconds (Hit CTRL+C to cancel)..');
        sleep(5);

        $objPHPExcel = $this->loadSheet();

        $lastRow = $this->getRowCount($objPHPExcel);

        for($row=2; $row<$lastRow; $row++) {

            $name = $this->getValue($objPHPExcel, 'Caregiver Name', $row);
            if ($name) {
                $this->output->writeln('Found caregiver: ' . $name);

                $data['firstname'] = $this->getValue($objPHPExcel, 'First Name', $row);
                $data['lastname'] = $this->getValue($objPHPExcel, 'Last Name', $row);
                $data['ssn'] = $this->getValue($objPHPExcel, 'SSN', $row);
                $data['title'] = $this->getValue($objPHPExcel, 'Classification', $row);
                $data['date_of_birth'] = $this->getValue($objPHPExcel, 'Date of Birth', $row);
                $data['password'] = bcrypt(str_random(12));
                $addressData['address1'] = $this->getValue($objPHPExcel, 'Address1', $row);
                $addressData['address2'] = $this->getValue($objPHPExcel, 'Address2', $row);
                $addressData['city'] = $this->getValue($objPHPExcel, 'City', $row);
                $addressData['state'] = $this->getValue($objPHPExcel, 'State', $row);
                $addressData['zip'] = $this->getValue($objPHPExcel, 'Zip', $row);
                $addressData['country'] = 'US';
                $addressData['type'] = 'home';

                $phone1 = $this->getValue($objPHPExcel, 'Phone1', $row);
                $phone2 = $this->getValue($objPHPExcel, 'Phone2', $row);
                $email = trim($this->getValue($objPHPExcel, 'Email', $row));

                // Prevent Duplicates
                if ($email && User::where('email', $email)->exists()) {
                    continue;
                }

                // Create caregiver record
                // Fake username and email
                $data['username'] = 'placeholder' . uniqid();
                $data['email'] = 'placeholder' . uniqid();
                $caregiver = new Caregiver($data);
                if ($email) {
                    $caregiver->email = $email;
                    $caregiver->username = $email;
                }
                $caregiver->save();
                if (!$email) {
                    $caregiver->email = $caregiver->id . '@noemail.allyms.com';
                    $caregiver->username = $caregiver->id . '@noemail.allyms.com';
                    $caregiver->save();
                }

                // Save caregiver to business
                $business->caregivers()->save($caregiver);

                // Create Address
                $address = new Address($addressData);
                $caregiver->addresses()->save($address);

                // Create phone number(s)
                try {
                    if ($phone1) {
                        $phone = new PhoneNumber();
                        $phone->input($phone1);
                        $phone->type = 'work';
                        $caregiver->phoneNumbers()->save($phone);
                    }
                    if ($phone2) {
                        $phone = new PhoneNumber();
                        $phone->input($phone2);
                        $phone->type = 'home';
                        $caregiver->phoneNumbers()->save($phone);
                    }
                }
                catch(\Exception $e) {

                }
            }

        }

    }
}
