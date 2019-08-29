<?php
namespace App\Reports;

use App\Contracts\Report;
use App\Shift;
use Carbon\Carbon;
use File;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_IOFactory;

abstract class BaseReport implements Report
{
    const CSV_DELIMITER = ';';
    const CSV_REPLACE_DELIMITER_WITH = ',';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rows;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var array
     */
    protected $formatters = [];

    /**
     * @var string
     */
    protected $dateField = "checked_in_time";

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
            $this->query()->whereBetween($this->dateField, [$start, $end]);
        }
        elseif ($start) {
            $this->query()->where($this->dateField, '>=', $start);
        }
        else {
            $this->query()->where($this->dateField, '<=', $end);
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
    abstract protected function results();

    /**
     * Public method to retrieve rows
     *
     * @return \Illuminate\Support\Collection|static
     */
    public function rows() {
        if ($this->rows) {
            return $this->format($this->rows);
        }

        return $this->format($this->rows = $this->results());
    }

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
     * Return an array of the rows
     *
     * @return array
     */
    public function toArray()
    {
        $this->setArrayFormat();
        $rows = $this->rows();
        return $rows->toArray();
    }

    /**
     * Return a CSV format of the report data
     *
     * @return string
     */
    public function toCsv()
    {
        $rows = $this->toArray();

        if (!count($rows)) {
            return '';
        }

        $csv = [];
        foreach($rows as $row) {
            $data = array_map(
                function($value) {
                    return (is_string($value)) ? str_replace(self::CSV_DELIMITER, self::CSV_REPLACE_DELIMITER_WITH, $value) : $value;
                },
                $row
            );
            $csv[] = implode(self::CSV_DELIMITER, $data);
        }

        return implode("\n", $csv);
    }

    /**
     * Start a download of a spreadsheet export of the report
     */
    public function download()
    {
        return Excel::create($this->getDownloadName(), function( $excel ) {

            $excel->sheet('Sheet1', function( $sheet ) {

                $data = $this->setHeadersFormat()
                             ->setNumericToFloatFormat()
                             ->setScalarFilter()
                             ->toArray();

                $sheet->fromArray( $data, null, 'A1', true );
            });

        })->export( 'xls' );
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

    protected function format(Collection $rows)
    {
        return $rows->map(function ($row) {
            foreach($this->formatters as $formatter) {
                $row = $formatter($row);
            }
            return $row;
        });
    }

    /**
     * Convert snake_case headers to Snake Case
     */
    public function setHeadersFormat()
    {
        $this->formatters['headers'] = function( $row ) {

            $formatted = [];
            foreach( $row as $key => $value ) {

                if ($key === 'id') $key = 'ID';
                $key = ucwords(str_replace('_', ' ', $key));
                $formatted[$key] = $value;
            }
            return $formatted;
        };
        return $this;
    }

    /**
     * Set all rows to have an array type
     */
    public function setArrayFormat()
    {
        $this->formatters['array'] = function($row) {
            if (is_array($row)) return $row;
            if (method_exists($row, 'toArray')) return $row->toArray();
            return (array) $row;
        };
        return $this;
    }

    /**
     * Convert all booleans to integers (false => 0, true => 1)
     *
     * @return $this
     */
    public function setBoolToIntFormat()
    {
        $this->formatters['bool_to_int'] = function($row) {
            return array_map(function($value) {
                return (is_bool($value)) ? (int) $value : $value;
            }, $row);
        };
        return $this;
    }

    /**
     * Convert all numeric non-integer values to floats
     *
     * @return $this
     */
    public function setNumericToFloatFormat()
    {
        $this->formatters['float_typing'] = function($row) {
            return array_map(function($value) {
                return (!is_int($value) && is_numeric($value)) ? (float) $value : $value;
            }, $row);
        };
        return $this;
    }

    /**
     * Format all date time values to a specified format and timezone
     *
     * @param $format
     * @param string $timezone
     * @return $this
     */
    public function setDateFormat($format, $timezone = 'UTC')
    {
        $this->setArrayFormat();
        $this->formatters['date'] = function($row) use ($format, $timezone) {
            return array_map(function($value) use ($format, $timezone) {
                if (
                    is_string($value) && str_contains($value, ':')
                    && ($strtotime = strtotime($value)) && $strtotime >= 1483228800
                ) {
                    $value = Carbon::createFromTimestampUTC($strtotime);
                }
                if ($value instanceof \DateTime) {
                    return $value->setTimezone(new \DateTimeZone($timezone))->format($format);
                }
                return $value;
            }, $row);
        };
        return $this;
    }

    /**
     * Filter non-scalar values from the array (useful for exports)
     *
     * @return $this
     */
    function setScalarFilter()
    {
        $this->setArrayFormat();
        $this->formatters['scalar'] = function($row) {
            return array_filter($row, 'is_scalar');
        };

        return $this;
    }

}
