<?php

namespace App\Console\Commands;

use DB;
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
        die("test");

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

}
