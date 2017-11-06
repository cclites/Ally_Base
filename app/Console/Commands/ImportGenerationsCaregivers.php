<?php

namespace App\Console\Commands;

use App\Business;
use App\PhoneNumber;
use Illuminate\Console\Command;
use PHPExcel_IOFactory;

class ImportGenerationsCaregivers extends Command
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
        $this->output->writeln('Importing prospects into ' . $business->name . ' in 5 seconds (Hit CTRL+C to cancel)..');
        sleep(5);

        if (!$objPHPExcel = PHPExcel_IOFactory::load($this->argument('file'))) {
            $this->output->error('Could not load file: ' . $this->argument('file'));
            exit;
        }

        $lastRow = (int) $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }

        for($row=2; $row<$lastRow; $row++) {

            $name = $this->getValue($objPHPExcel, 'Caregiver Name', $row);
            if ($name) {

                // TODO: Prevent Duplicates

                $this->output->writeln('Found caregiver: ' . $name);

                $data['firstname'] = $this->getValue($objPHPExcel, 'First Name', $row);
                $data['lastname'] = $this->getValue($objPHPExcel, 'Last Name', $row);
                $data['ssn'] = $this->getValue($objPHPExcel, 'SSN', $row);
                $data['title'] = $this->getValue($objPHPExcel, 'Classification', $row);
                $data['date_of_birth'] = $this->getValue($objPHPExcel, 'Date of Birth', $row);
                $address['address1'] = $this->getValue($objPHPExcel, 'Address1', $row);
                $address['address2'] = $this->getValue($objPHPExcel, 'Address2', $row);
                $address['city'] = $this->getValue($objPHPExcel, 'City', $row);
                $address['state'] = $this->getValue($objPHPExcel, 'State', $row);
                $address['zip'] = $this->getValue($objPHPExcel, 'Zip', $row);
                $address['country'] = 'US';

                $phone1 = $this->getValue($objPHPExcel, 'Phone1', $row);
                $phone2 = $this->getValue($objPHPExcel, 'Phone2', $row);

                // TODO: Create caregiver record

                // TODO: Save caregiver to business

                // TODO: Create address

                // TODO: Create phone number(s)

            }

        }

    }

    public function getValue(\PHPExcel $PHPExcel, $header, $rowNo)
    {
        $column = $this->findColumn($PHPExcel, $header);
        if ($column === false) {
            return null;
        }

        $value = $PHPExcel->getActiveSheet()->getCell($column . $rowNo)->getCalculatedValue();
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
