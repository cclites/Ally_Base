<?php

namespace App\Console\Commands;

use App\Note;
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
        // sleep(3);

        $this->loadSheet();
        $lastRow = (int) $this->getRowCount($this->sheet);
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }

        DB::beginTransaction();

        $count = 0;
        for($row=2; $row <= $lastRow; $row++) {

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
        if ($exportedId = $this->resolve('ID', $row)) {
            $model->setMeta('Exported_ID', $exportedId);
        }

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
                $phone = PhoneNumber::fromInput($type, $number);
                $phone->number(); // This should throw an exception if invalid format
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
            if (strcasecmp(trim($value), $header) === 0) {
                return $column;
            }
        }

        return false;
    }

    /**
     * Resolve a value matching the field header and row number
     *
     * @param string $field
     * @param int $row
     * @return false|mixed|null|string
     * @throws \PHPExcel_Exception
     */
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
     * Resolve a field that expects a boolean type, matches true as: true, 1, 'Y', 'Yes', 'True'
     *
     * @param string $field
     * @param int $row
     * @param bool $default
     * @param array $additionalTrueStrings
     * @param array $additionalFalseStrings
     * @return bool
     * @throws \PHPExcel_Exception
     */
    public function resolveBoolean(string $field, int $row, $default = false, array $additionalTrueStrings = [], array $additionalFalseStrings = [])
    {
        $cellValue = $this->getValue($this->sheet, $field, $row);

        $trueStrings = array_merge(
            ['Y', 'YES', 'TRUE'],
            array_map('strtoupper', $additionalTrueStrings)
        );
        $falseStrings = array_merge(
            ['N', 'NO', 'FALSE'],
            array_map('strtoupper', $additionalFalseStrings)
        );

        if (is_numeric($cellValue) || is_bool($cellValue)) {
            return (bool) $cellValue;
        }
        if (in_array(strtoupper($cellValue), $trueStrings)) {
            return true;
        }
        if (in_array(strtoupper($cellValue), $falseStrings)) {
            return false;
        }

        return $default;
    }

    /**
     * Allows values like: false, true, 0, 1, 'INACTIVE', 'ACTIVE', 'I', 'A', 'FALSE', 'TRUE'
     *
     * @param int $row
     * @param $cellValue
     * @return bool
     */
    protected function resolveActive(int $row, $cellValue)
    {
        return $this->resolveBoolean(
            'Active',
            $row,
            true,
            ['A', 'ACTIVE'],
            ['I', 'INACTIVE']
        );
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

    /**
     * Resolve the date of birth in YYYY-MM-DD or MM/DD/YYYY formats.
     *
     * @param int $row
     * @param $cellValue
     * @return null|string
     */
    protected function resolveDateOfBirth(int $row, $cellValue)
    {
        return $this->transformDateValue($cellValue);
    }

    /**
     * Resolve Gender into a single letter (M/F)
     *
     * @param int $row
     * @param $cellValue
     * @return null|string
     */
    protected function resolveGender(int $row, $cellValue)
    {
        return is_string($cellValue) ? strtoupper(substr($cellValue, 0, 1)) : null;
    }


    /**
     * Transform dates to YYYY-MM-DD
     *
     * @param string $value
     * @return null|string
     */
    protected function transformDateValue($value)
    {
        if (!$value) return null;

        try {
            if (Carbon::createFromFormat('Y-m-d', $value) !== false) {
                return $value;
            }
        } catch (\Exception $e) {}

        try {
            if ($parsed = Carbon::parse($value)) {
                return $parsed->format('Y-m-d');
            }
        } catch (\Exception $e) {}

        return null;
    }

}
