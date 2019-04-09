<?php

namespace App\Reports;

use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollExportReport extends BusinessResourceReport
{
    const ADP = 'ADP';
    const PAYCHEX = 'PAYCHEX';
    const BCN = 'BCN';

    /**
     * The payroll export format.
     *
     * @var int
     */
    protected $format;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->query = Shift::with(['caregiver', 'client'])
            ->whereConfirmed();
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
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @param string $timezone
     * @return $this
     */
    public function forDates(string $start, string $end, ?string $timezone = null) : self
    {
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }
        $startDate = new Carbon($start . ' 00:00:00', $timezone);
        $endDate = new Carbon($end . ' 23:59:59', $timezone);
        $this->between($startDate, $endDate);

        return $this;
    }

    /**
     * Set the output format.
     *
     * @param string $format
     * @return $this
     */
    public function inFormat(string $format) : self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        $data = $this->query()
            ->get()
            ->map(function (Shift $row) {
                return [
                    'ssn' => $row->caregiver->ssn,
                    'name' => $row->caregiver->name,
                    'paycode' => $row->getPaycode(),
                    'hours' => $row->duration(),
                    'amount' => $row->costs()->getCaregiverCost(),
                    'location' => optional($row->client->evvAddress)->zip,

                    'caregiver_id' => $row->caregiver_id,
                    'caregiver_last_name' => $row->caregiver->lastname,
                    'caregiver_first_name' => $row->caregiver->firstname,
                ];
            });

        switch ($this->format) {
            case self::BCN:
                return $this->formatBCN($data);
            case self::ADP:
            case self::PAYCHEX:
                // ADP & PAYCHEX have the same format
            default:
                return $this->formatADP($data);
        }
    }

    /**
     * Format the report data for ADP.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function formatADP(Collection $data) : Collection
    {
        $results = [];

        $data->groupBy(['caregiver_id', 'paycode'])
            ->each(function ($cgRow) use (&$results) {
                $cgRow->each(function ($typeRow) use (&$results) {
                    array_push($results, [
                        'ssn' => $typeRow[0]['ssn'],
                        'name' => $typeRow[0]['name'],
                        'paycode' => $typeRow[0]['paycode'],
                        'hours' => $typeRow->sum('hours'),
                        'amount' => $typeRow->sum('amount'),
                        'dept' => '',
                        'division' => '',
                        'location' => $typeRow[0]['location'],

                        'caregiver_id' => $typeRow[0]['caregiver_id'],
                        'caregiver_last_name' => $typeRow[0]['caregiver_last_name'],
                        'caregiver_first_name' => $typeRow[0]['caregiver_first_name'],
                    ]);
                });
            });

        return collect($results)
            ->sortBy('caregiver_last_name')
            ->values();
    }

    /**
     * Format the report data for Paychex.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function formatPaychex(Collection $data) : Collection
    {
        return $this->formatADP($data);
    }

    /**
     * Format the report data for BCN.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function formatBCN(Collection $data) : Collection
    {
        $results = [];

        $data->groupBy(['caregiver_id', 'location', 'paycode'])
            ->each(function ($cgGroup) use (&$results) {
                $cgGroup->each(function ($zipGroup) use (&$results) {
                    $zipGroup->each(function ($typeRow) use (&$results) {
                        $results[] = [
                            'ssn' => $typeRow[0]['ssn'],
                            'name' => $typeRow[0]['name'],
                            'paycode' => $typeRow[0]['paycode'],
                            'hours' => $typeRow->sum('hours'),
                            'amount' => $typeRow->sum('amount'),
                            'dept' => '',
                            'division' => '',
                            'location' => $typeRow[0]['location'],

                            'caregiver_id' => $typeRow[0]['caregiver_id'],
                            'caregiver_last_name' => $typeRow[0]['caregiver_last_name'],
                            'caregiver_first_name' => $typeRow[0]['caregiver_first_name'],
                        ];
                    });
                });
            });

        return collect($results)->sortBy('caregiver_last_name')->values();
    }

    /**
     * Format the report data to CSV.
     *
     * @return string
     */
    public function toCsv() : string
    {
        $rows = $this->rows()->map(function ($item) {
            return array_merge(array_except($item, [
                'caregiver_id',
                'caregiver_first_name',
                'caregiver_last_name'
            ]), [
                'hours' => number_format($item['hours'], 1),
                'amount' => number_format($item['amount'], 2)
            ]);
        });

        if (empty($rows)) {
            return '';
        }

        // Build header
        $headerRow = collect($rows[0])
            ->keys()
            ->map(function ($key) {
                return $key === 'id' ? 'ID' : ucwords(str_replace('_', ' ', $key));
            })
            ->toArray();

        // Build rows
        $csv[] = '"' . implode('","', $headerRow) . '"';
        foreach($rows as $row) {
            $csv[] = '"' . implode('","', $row) . '"';
        }

        return implode("\r\n", $csv);
    }

    /**
     * Download CSV of report.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadCsv()
    {
        return \Response::make($this->toCsv(), 200, [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Payroll-Export-Report.csv"',
        ]);
    }
}
