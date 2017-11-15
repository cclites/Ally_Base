<?php
namespace App\Reports;

use App\CaregiverLicense;
use App\Contracts\Report;
use Carbon\Carbon;

class CertificationExpirationReport implements Report
{
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
     * ScheduledPaymentsReport constructor.
     */
    public function __construct()
    {
        $this->query = CaregiverLicense::with('caregiver');
    }

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
        $this->query->where($field, $delimiter, $value);
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
        if ($start) {
            $start = (new Carbon($start))->setTimezone('UTC');
        }
        if ($end) {
            $end = (new Carbon($end))->setTimezone('UTC');
        }

        if ($start && $end) {
            $this->query->whereBetween('expires_at', [$start, $end]);
        }
        elseif ($start) {
            $this->query->where('expires_at', '>=', $start);
        }
        else {
            $this->query->where('expires_at', '<=', $end);
        }
        return $this;
    }

    /**
     * Specify the sort order for the report
     *
     * @param $column
     * @param string $direction  ASC | DESC
     * @return $this
     */
    public function orderBy($column, $direction='ASC')
    {
        $this->query()->orderBy($column, $direction);
        return $this;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        if (!$this->generated) {
            $licenses = $this->query->get();
            $this->rows = $licenses->map(function(CaregiverLicense $license) {
                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'expiration_date' => (new Carbon($license->expires_at))->format('Y-m-d'),
                    'caregiver_id' => $license->caregiver->id,
                    'caregiver_name' => $license->caregiver->nameLastFirst(),
                ];
            });
        }
        return $this->rows;
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
}