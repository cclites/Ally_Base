<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Shift;
use Carbon\Carbon;
use File;
use PHPExcel_IOFactory;

abstract class BaseReport implements Report
{
    const CSV_DELIMITER = ';';
    const CSV_REPLACE_DELIMITER_WITH = ',';

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Add a condition to limit report data
     *
     * @param $field
     * @param $delimiter
     * @param null $value
     * @return $this
     */
    public function where($field, $delimiter, $value = null)
    {
        $this->query()->where($field, $delimiter, $value);
        return $this;
    }

    /**
     * Limit rows between two dates
     *
     * @param string|\DateTime|null $start If null, leave starting period unlimited
     * @param string|\DateTime|null $end If null, leave ending period unlimited
     * @return $this
     */
    public function between($start = null, $end = null)
    {
        if ($start instanceof \DateTime) {
            $start = Carbon::instance($start)->setTimezone('UTC');
        }
        if ($end instanceof \DateTime) {
            $end = Carbon::instance($end)->setTimezone('UTC');
        }

        if ($start && $end) {
            $this->query()->whereBetween('checked_in_time', [$start, $end]);
        }
        elseif ($start) {
            $this->query()->where('checked_in_time', '>=', $start);
        }
        else {
            $this->query()->where('checked_in_time', '<=', $end);
        }

        return $this;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function query();

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public function rows();

    /**
     * Count the number of rows
     *
     * @return int
     */
    public function count()
    {
        return $this->rows()->count();
    }

    /**
     * Return the sum of a column
     *
     * @return float
     */
    public function sum($column)
    {
        return $this->rows()->sum($column);
    }

    /**
     * Specify the sort order for the report
     *
     * @param $column
     * @param string $direction ASC | DESC
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->query()->orderBy($column, $direction);
        return $this;
    }

    /**
     * Return a CSV format of the report data
     *
     * @return string
     */
    public function toCsv()
    {
        $rows = $this->rows();

        if (!$rows) {
            return '';
        }

        $headers = array_map(
            function($value) {
                return (is_string($value)) ? str_replace(self::CSV_DELIMITER, self::CSV_REPLACE_DELIMITER_WITH, $value) : $value;
            },
            array_keys($rows->first())
        );
        $csv = [implode(self::CSV_DELIMITER, $headers)];

        foreach($rows as $row) {
            $data = array_map(
                function($value) {
                    return (is_string($value)) ? str_replace(self::CSV_DELIMITER, self::CSV_REPLACE_DELIMITER_WITH, $value) : $value;
                },
                $row
            );
            $csv[] = [implode(self::CSV_DELIMITER, $data)];
        }

        return implode("\n", $csv);
    }

    /**
     * Start a download of a spreadsheet export of the report
     *
     * @return void
     */
    public function download()
    {
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $this->getDownloadName() . '.xlsx";');

        $csvFile = tempnam(sys_get_temp_dir(), 'export');
        File::put($csvFile, $this->toCsv());

        $PHPExcel = PHPExcel_IOFactory::load($csvFile);
        unlink($csvFile);

        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Return the name of the downloaded file
     *
     * @return string
     */
    public function getDownloadName()
    {
        return 'Report';
    }
}