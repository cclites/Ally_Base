<?php
namespace App\Imports;

use PHPExcel_IOFactory;
use PHPExcel_Reader_IReader;

class Worksheet
{
    /**
     * @var \PHPExcel
     */
    protected $PHPExcel;

    /**
     * @var \PHPExcel_Worksheet
     */
    protected $sheet;

    /**
     * Convert empty strings to null values in getValue()
     *
     * @var bool
     */
    protected $allowEmptyStrings = false;

    public function __construct($file, PHPExcel_Reader_IReader $reader = null)
    {
        if ($reader) {
            $this->PHPExcel = $reader->load($file);
        }
        else {
            $this->PHPExcel = PHPExcel_IOFactory::load($file);
        }
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function loadSheet($name = null)
    {
        if (!$name) {
            $this->sheet = $this->PHPExcel->getActiveSheet();
        }
        else {
            $this->sheet = $this->PHPExcel->getSheetByName($name);
        }
        return $this->sheet;
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function sheet()
    {
        if (!$this->sheet) {
            $this->sheet = $this->loadSheet();
        }
        return $this->sheet;
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        $lastRow = (int) $this->sheet()->getHighestRow();
        if (!$lastRow) {
            return 0;
        }
        return $lastRow;
    }

    public function isRowEmpty($rowNo)
    {
        // Only checks columns A-Z
        $a_z = range('A', 'Z');
        foreach($a_z as $column) {
            $value = $this->sheet->getCell($column . $rowNo)->getValue();
            if ($value !== null && (is_string($value) && trim($value) !== '')) {
                return false;
            }
        }
        return true;
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
            //return date('Y-m-d H:i:s', \PHPExcel_Shared_Date::ExcelToPHP($value));
            return date('Y-m-d H:i:s', strtotime($value));
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