<?php

namespace App\Console\Commands;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PHPExcel_IOFactory;


class ImportShiftReconciliation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shift_reconcil {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an internal shift reconciliation spreadsheet into the system.';

    /**
     * Convert empty strings to null values in getValue()
     *
     * @var bool
     */
    protected $allowEmptyStrings = false;

    /**
     * @var \PHPExcel
     */
    protected $file;

    /**
     * @var \PHPExcel_Worksheet
     */
    protected $sheet;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $objPHPExcel = $this->loadFile();

        $this->importShifts();

    }

    public function importShifts()
    {
        $sheet = $this->loadSheet('SHIFTS');

        if (!$sheet) {
            $this->output->error('Error loading "SHIFTS" sheet.');
            exit();
        }

        $lastRow = $this->getRowCount();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }

        for ($row = 2; $row < $lastRow; $row++) {

            $data['business_id'] = $this->getValue('Business ID', $row);
            $data['client_id'] = $this->getValue('Client ID', $row);
            $data['caregiver_id'] = $this->getValue('CG ID', $row);
            $data['caregiver_rate'] = floatval($this->getValue('CG Rate', $row));
            $data['provider_fee'] = floatval($this->getValue('Provider Fee', $row));
            $data['hours_type'] = $this->getValue('Provider Fee', $row) ?? 'default';
            $clockIn = $this->getValue('Clocked-In', $row);  // This is in business timezone!!
            $hours  = $this->getValue('Duration', $row);

            try {
                $business = Business::findOrFail($data['business_id']);
                $caregiver = Caregiver::findOrFail($data['caregiver_id']);
                $client = Client::findOrFail($data['caregiver_id']);
            }
            catch(\Exception $e) {
                $this->output->error('Shifts Row ' . $row . ': could not find a relationship... '  . $e->getMessage());
            }

            $clockIn = Carbon::createFromFormat('Y-m-d H:i:s', $clockIn, $business->timezone)->setTimezone('UTC');
            $data['checked_in_time'] = $clockIn->format('Y-m-d H:i:s');
            $data['checked_out_time'] = $clockIn->copy()->addMinutes(round($hours * 60))->format('Y-m-d H:i:s');

            // Check for existing shift
            $oldShift = Shift::where('business_id', $data['business_id'])
                ->where('client_id', $data['client_id'])
                ->where('caregiver_id', $data['caregiver_id'])
                ->whereBetween('checked_in_time', [
                    $clockIn->copy()->subMinutes(30),
                    $clockIn->copy()->add(30)
                ]);

            if ($oldShift->exists()) {
                $this->output->warning('Shifts Row ' . $row . ': Found existing shift ' . $oldShift->first()->id . ' conflicting with ' . $data['checked_in_time']);
            }

            print_r($data);
            sleep(2);
        }
    }
    /**
     * @return \PHPExcel
     */
    public function loadFile()
    {
        if (!$objPHPExcel = PHPExcel_IOFactory::load($this->argument('file'))) {
            $this->output->error('Could not load file: ' . $this->argument('file'));
            exit;
        }
        $this->file = $objPHPExcel;
        return $this->file;
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function loadSheet($name)
    {
        $this->sheet = $this->file->getSheetByName($name);
        return $this->sheet;
    }


    public function getRowCount()
    {
        $lastRow = (int) $this->sheet->getHighestRow();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }
        return $lastRow;
    }

    public function getValue($header, $rowNo)
    {
        $column = $this->findColumn($header);
        if ($column === false) {
            return null;
        }
        $cell = $this->sheet->getCell($column . $rowNo);
        $value = $cell->getValue();

        if(\PHPExcel_Shared_Date::isDateTime($cell)) {
            return date('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value));
        }

        if (!$this->allowEmptyStrings && is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }

    public function findColumn($header)
    {
        // Get range from A to BZ
        $a_z = range('A', 'Z');
        $range = array_merge(
            $a_z,
            array_map(function($val) { return 'A' . $val; }, $a_z),
            array_map(function($val) { return 'B' . $val; }, $a_z)
        );

        foreach($range as $column) {
            $value = $this->sheet->getCell($column . '1')->getValue();
            if ($value == $header) {
                return $column;
            }
        }

        return false;
    }
}
