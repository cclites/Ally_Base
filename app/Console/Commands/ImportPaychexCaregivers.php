<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Caregiver;
use App\PhoneNumber;
use App\User;
use Illuminate\Support\Str;

class ImportPaychexCaregivers extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:paychex_caregivers {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a "Paychex" excel export of caregivers into the system.';

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

        for($row=2; $row<=$lastRow; $row++) {

            $paychexId = $this->getValue($objPHPExcel, 'Paychex ID', $row);
            $this->output->writeln("Row $row. Paychex ID: $paychexId.");
            if ($paychexId) {
                $data['firstname'] = $this->getValue($objPHPExcel, 'First Name', $row);
                $data['lastname'] = $this->getValue($objPHPExcel, 'Last Name', $row);
                $this->output->writeln('Found caregiver: ' . $data['firstname'] . ' ' . $data['lastname']);
                $data['ssn'] = str_pad($this->getValue($objPHPExcel, 'SSN', $row), 9, '0', STR_PAD_LEFT);
                $data['title'] = $this->getValue($objPHPExcel, 'Classification', $row);
                $data['date_of_birth'] = filter_date($this->getValue($objPHPExcel, 'Date of Birth', $row));
                $data['password'] = bcrypt(Str::random(12));
                $data['hire_date'] = filter_date($this->getValue($objPHPExcel, 'Hire Date', $row));
                $data['gender'] = $this->getValue($objPHPExcel, 'Gender', $row);
                $addressData['address1'] = $this->getValue($objPHPExcel, 'Address 1', $row);
                $addressData['address2'] = $this->getValue($objPHPExcel, 'Address 2', $row);
                $addressData['city'] = $this->getValue($objPHPExcel, 'City', $row);
                $addressData['state'] = $this->getValue($objPHPExcel, 'State', $row);
                $addressData['zip'] = $this->getValue($objPHPExcel, 'Zip', $row);
                $addressData['country'] = 'US';
                $addressData['type'] = 'home';

                $phone1 = $this->getValue($objPHPExcel, 'Phone 1', $row);
                $phone2 = $this->getValue($objPHPExcel, 'Phone 2', $row);
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
                $business->assignCaregiver($caregiver);

                // Create Address
                $address = new Address($addressData);
                $caregiver->addresses()->save($address);

                // Create phone number(s)
                try {
                    if ($phone1) {
                        $phone = new PhoneNumber();
                        $phone->input($phone1);
                        $phone->type = 'primary';
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
                    dd($phone1, $phone2);
                }
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
        // TODO: Implement warningMessage() method.
    }

    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\Business
     */
    protected function business()
    {
        // TODO: Implement business() method.
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
        // TODO: Implement importRow() method.
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        // TODO: Implement emptyRow() method.
    }
}
