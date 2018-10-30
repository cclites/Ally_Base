<?php

namespace App\Console\Commands;

use App\PhoneNumber;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
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
     * The message to show before executing the import
     *
     * @return string
     */
    abstract protected function warningMessage();

    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\Business
     */
    abstract protected function business();

    /**
     * Import the specified row of data from the sheet and return the related model
     *
     * @param int $row
     * @return \Illuminate\Database\Eloquent\Model|false
     * @throws \Exception
     */
    abstract protected function importRow(int $row);

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    abstract protected function emptyRow(int $row);

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->writeln($this->warningMessage() . ' (Hit CTRL+C to cancel)..');
        sleep(3);

        $this->loadSheet();
        $lastRow = (int) $this->getRowCount($this->sheet);
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }

        DB::beginTransaction();

        $count = 0;
        for($row=2; $row<$lastRow; $row++) {
            if (!$this->emptyRow($row)) {
                if ($imported = $this->importRow($row)) {
                    $count++;
                }
            }
        }

        DB::commit();
        $this->output->writeln($count . ' rows imported.');
    }

    /**
     * Import meta data from the "meta" option
     *
     * @param Model $model
     * @param int $row
     */
    protected function importMeta(Model $model, int $row)
    {
        if ($metaFields = $this->option('meta')) {
            $metaFields = array_map('trim', explode(',', $metaFields));
            foreach($metaFields as $field) {
                $value = $this->resolve($field, $row);
                if (strlen($value)) {
                    $model->setMeta($field, $value);
                }
            }
        }
    }

    /**
     * @param Model $model
     * @param int $row
     */
    protected function importPhoneNumbers(Model $model, int $row)
    {
        try {
            $phoneFields = ['primary' => 'Phone1', 'home' => 'Phone2'];
            foreach ($phoneFields as $type => $phoneField) {
                $number = preg_replace('/[^\d\-]/', '', $this->resolve($phoneField, $row));
                $phone = new PhoneNumber(['type' => $type]);
                $phone->input($number);
                $phone->notes = $this->resolve("${phoneField}Notes",  $row); // ex. Phone1Notes
                $model->phoneNumbers()->save($phone);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Import Office Note
     *
     * @param int $row
     * @param Model $model
     */
    protected function importNotes(Model $model, int $row)
    {
        if ($officeNote = $this->resolve("OfficeNote", $row)) {
            $officeUser = $this->business()->users()->first();
            $model->notes()->save(new Note([
                'body' => $officeNote . "\n\nImported on " . date('F j, Y'),
                'created_by' => $officeUser->id,
                'business_id' => $this->business()->id,
            ]));
        }
    }

    /**
     * Load the import spreadsheet into $sheet
     *
     * @return \PHPExcel
     * @throws \PHPExcel_Reader_Exception
     */
    public function loadSheet()
    {
        if (!$objPHPExcel = PHPExcel_IOFactory::load($this->argument('file'))) {
            $this->output->error('Could not load file: ' . $this->argument('file'));
            exit;
        }
        return $this->sheet = $objPHPExcel;
    }

    /**
     * Get the row count from the spreadsheet
     *
     * @param \PHPExcel $PHPExcel
     * @return int
     * @throws \PHPExcel_Exception
     */
    public function getRowCount(\PHPExcel $PHPExcel)
    {
        $lastRow = (int) $PHPExcel->setActiveSheetIndex(0)->getHighestRow();
        return $lastRow;
    }

    /**
     * Get the cell value at row $rowNo with a header matching $header
     *
     * @param \PHPExcel $PHPExcel
     * @param $header
     * @param $rowNo
     * @return false|mixed|null|string
     * @throws \PHPExcel_Exception
     */
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

        return is_string($value) ? trim($value) : $value;
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
            if (strcasecmp($value, $header) === 0) {
                return $column;
            }
        }

        return false;
    }

    public function resolve(string $field, int $row)
    {
        $cellValue = $this->getValue($this->sheet, $field, $row);

        $methodName = 'resolve' . studly_case($field);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($row, $cellValue);
        }

        return $cellValue;
    }

    /**
     * Allows values like: false, true, 0, 1, 'INACTIVE', 'ACTIVE', 'I', 'A', 'FALSE', 'TRUE'
     *
     * @param int $row
     * @param $cellValue
     * @return int|null
     */
    protected function resolveActive(int $row, $cellValue)
    {
        if (is_numeric($cellValue) || is_bool($cellValue)) {
            return (int)$cellValue;
        }

        if (strlen($cellValue)) {
            $validStrings = ['A', 'ACTIVE', 'TRUE'];
            if (in_array(strtoupper($cellValue), $validStrings)) {
                return 1;
            }
            return 0;
        }

        return null;
    }

    /**
     * Resolve and validate emails
     *
     * @param int $row
     * @param $cellValue
     * @return null
     */
    protected function resolveEmail(int $row, $cellValue)
    {
        if (filter_var($cellValue, FILTER_VALIDATE_EMAIL)) {
            return $cellValue;
        }
        return null;
    }

    /**
     * Resolves "First Name" or get the first name from a "Name" column
     *
     * @param int $row
     * @param $cellValue
     * @return mixed
     */
    protected function resolveFirstName(int $row, $cellValue)
    {
        if ($cellValue) {
            return $cellValue;
        }

        if ($name = $this->getNameArray($row)) {
            return $name['first'];
        }
    }

    /**
     * Resolves "Last Name" or get the last name from a "Name" column
     *
     * @param int $row
     * @param $cellValue
     * @return mixed
     */
    protected function resolveLastName(int $row, $cellValue)
    {
        if ($cellValue) {
            return $cellValue;
        }

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
            } else {
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

    /**
     * Resolves the state from abbreviation or full name
     *
     * @param int $row
     * @param $cellValue
     * @return string
     */
    protected function resolveState(int $row, $cellValue)
    {
        if (strlen($cellValue) > 2) {
            $states = [
                'AL' => 'ALABAMA',
                'AK' => 'ALASKA',
                'AS' => 'AMERICAN SAMOA',
                'AZ' => 'ARIZONA',
                'AR' => 'ARKANSAS',
                'CA' => 'CALIFORNIA',
                'CO' => 'COLORADO',
                'CT' => 'CONNECTICUT',
                'DE' => 'DELAWARE',
                'DC' => 'DISTRICT OF COLUMBIA',
                'FM' => 'FEDERATED STATES OF MICRONESIA',
                'FL' => 'FLORIDA',
                'GA' => 'GEORGIA',
                'GU' => 'GUAM GU',
                'HI' => 'HAWAII',
                'ID' => 'IDAHO',
                'IL' => 'ILLINOIS',
                'IN' => 'INDIANA',
                'IA' => 'IOWA',
                'KS' => 'KANSAS',
                'KY' => 'KENTUCKY',
                'LA' => 'LOUISIANA',
                'ME' => 'MAINE',
                'MH' => 'MARSHALL ISLANDS',
                'MD' => 'MARYLAND',
                'MA' => 'MASSACHUSETTS',
                'MI' => 'MICHIGAN',
                'MN' => 'MINNESOTA',
                'MS' => 'MISSISSIPPI',
                'MO' => 'MISSOURI',
                'MT' => 'MONTANA',
                'NE' => 'NEBRASKA',
                'NV' => 'NEVADA',
                'NH' => 'NEW HAMPSHIRE',
                'NJ' => 'NEW JERSEY',
                'NM' => 'NEW MEXICO',
                'NY' => 'NEW YORK',
                'NC' => 'NORTH CAROLINA',
                'ND' => 'NORTH DAKOTA',
                'MP' => 'NORTHERN MARIANA ISLANDS',
                'OH' => 'OHIO',
                'OK' => 'OKLAHOMA',
                'OR' => 'OREGON',
                'PW' => 'PALAU',
                'PA' => 'PENNSYLVANIA',
                'PR' => 'PUERTO RICO',
                'RI' => 'RHODE ISLAND',
                'SC' => 'SOUTH CAROLINA',
                'SD' => 'SOUTH DAKOTA',
                'TN' => 'TENNESSEE',
                'TX' => 'TEXAS',
                'UT' => 'UTAH',
                'VT' => 'VERMONT',
                'VI' => 'VIRGIN ISLANDS',
                'VA' => 'VIRGINIA',
                'WA' => 'WASHINGTON',
                'WV' => 'WEST VIRGINIA',
                'WI' => 'WISCONSIN',
                'WY' => 'WYOMING',
                'AE' => 'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
                'AA' => 'ARMED FORCES AMERICA (EXCEPT CANADA)',
                'AP' => 'ARMED FORCES PACIFIC'
            ];

            return (string)array_search(strtoupper($cellValue), $states);
        }

        return strtoupper($cellValue);
    }

    protected function resolveDateOfBirth(int $row, $cellValue)
    {
        try {
            if (Carbon::createFromFormat('Y-m-d', $cellValue) !== false) {
                return $cellValue;
            }
        } catch (\Exception $e) {}

        try {
            if ($parsed = Carbon::parse($cellValue)) {
                return $parsed->format('Y-m-d');
            }
        } catch (\Exception $e) {}

        return null;
    }

}
