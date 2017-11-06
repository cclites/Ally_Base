<?php

namespace App\Console\Commands;

use App\Address;
use App\Business;
use App\Caregiver;
use App\PhoneNumber;
use App\User;
use Illuminate\Console\Command;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

abstract class BaseImport extends Command
{

    /**
     * @var \PHPExcel
     */
    protected $sheet;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $business = Business::findOrFail($this->argument('business_id'));
        $this->output->writeln('Importing caregivers into ' . $business->name . ' in 2 seconds (Hit CTRL+C to cancel)..');
        sleep(2);

        $objPHPExcel = $this->loadSheet();
        $lastRow = (int) $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }

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
                $data['username'] = 'placeholder' . time();
                $data['email'] = 'placeholder' . time();
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
                    dd($phone1, $phone2);
                }
            }

        }

    }

    /**
     * @return \PHPExcel
     */
    public function loadSheet()
    {
        if (!$objPHPExcel = PHPExcel_IOFactory::load($this->argument('file'))) {
            $this->output->error('Could not load file: ' . $this->argument('file'));
            exit;
        }
        return $this->sheet = $objPHPExcel;
    }

    public function getRowCount(\PHPExcel $PHPExcel)
    {
        $lastRow = (int) $PHPExcel->setActiveSheetIndex(0)->getHighestRow();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }
        return $lastRow;
    }

    public function getValue(\PHPExcel $PHPExcel, $header, $rowNo)
    {
        $column = $this->findColumn($PHPExcel, $header);
        if ($column === false) {
            return null;
        }
        $cell = $PHPExcel->getActiveSheet()->getCell($column . $rowNo);
        $value = $cell->getValue();

        if(PHPExcel_Shared_Date::isDateTime($cell)) {
            return date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($value));
        }

        return $value;
    }

    public function findColumn(\PHPExcel $PHPExcel, $header)
    {
        // Get range from A to BZ
        $a_z = range('A', 'Z');
        $range = array_merge(
            $a_z,
            array_map(function($val) { return 'A' . $val; }, $a_z),
            array_map(function($val) { return 'B' . $val; }, $a_z)
        );

        foreach($range as $column) {
            $value = $PHPExcel->getActiveSheet()->getCell($column . '1')->getValue();
            if ($value == $header) {
                return $column;
            }
        }

        return false;
    }
}
