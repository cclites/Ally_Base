<?php

namespace App\Reports;

use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PayrollExportReport extends BaseReport
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
     * Get the shift data rows.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getShiftResults() : Collection
    {
        return $this->query()
            ->get()
            ->map(function (Shift $row) {
                return [
                    'caregiver_id' => $row->caregiver_id,
                    'name' => $row->caregiver->name,
                    'paycode' => $row->getPaycode(),
                    'pay_rate' => (string) $row->getCaregiverRate(),
                    'hours' => $row->duration(),
                    'amount' => $row->costs()->getCaregiverCost(false),
                    'location' => optional($row->client->evvAddress)->zip,

                    'caregiver_last_name' => $row->caregiver->lastname,
                    'caregiver_first_name' => $row->caregiver->firstname,
                ];
            });
    }

    /**
     * Get the rows to represent the shift mileage expenses.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getShiftMileageResults() : Collection
    {
        return $this->query()
            ->where('mileage', '>', 0.00)
            ->get()
            ->map(function (Shift $row) {
                return [
                    'caregiver_id' => $row->caregiver_id,
                    'name' => $row->caregiver->name,
                    'paycode' => 'MIL',
                    'pay_rate' => $row->costs()->mileageCalculator()->getMileageRate(),
                    'hours' => $row->mileage,
                    'amount' => $row->costs()->getMileageCost(false),
                    'location' => optional($row->client->evvAddress)->zip,

                    'caregiver_last_name' => $row->caregiver->lastname,
                    'caregiver_first_name' => $row->caregiver->firstname,
                ];
            });
    }

    /**
     * Get the rows to represent the shift mileage expenses.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getShiftExpenseResults() : Collection
    {
        return $this->query()
            ->where('other_expenses', '>', 0.00)
            ->get()
            ->map(function (Shift $row) {
                return [
                    'caregiver_id' => $row->caregiver_id,
                    'name' => $row->caregiver->name,
                    'paycode' => 'EXP',
                    'pay_rate' => '0',
                    'hours' => '0',
                    'amount' => $row->costs()->getCaregiverExpenses(),
                    'location' => optional($row->client->evvAddress)->zip,

                    'caregiver_last_name' => $row->caregiver->lastname,
                    'caregiver_first_name' => $row->caregiver->firstname,
                ];
            });
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results() : ?iterable
    {
        $data = $this->getShiftResults();
        $data = $data->merge($this->getShiftMileageResults());
        $data = $data->merge($this->getShiftExpenseResults());

        // exclude rows that have a $0 total
        $data = $data->filter(function ($row) {
            return floatval($row['amount']) <> 0.00;
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
     * Map the report data.
     *
     * @param Collection $rows
     * @return array
     */
    public function mapReportRecord(Collection $rows) : array
    {
        $result = [
            'caregiver_id' => $rows[0]['caregiver_id'],
            'name' => $rows[0]['name'],
            'paycode' => $rows[0]['paycode'],
            'pay_rate' => $rows[0]['paycode'] == 'EXP' ? '-' : $rows[0]['pay_rate'],
            'hours' => $rows[0]['paycode'] == 'EXP' ? '-' : $rows->reduce(function ($carry, $item) {
                return add($carry, $item['hours'], 2);
            }, 0),
            'amount' => $rows->reduce(function ($carry, $item) {
                return add($carry, $item['amount'], 2);
            }, 0),
            'dept' => '',
            'division' => '',
            'caregiver_last_name' => $rows[0]['caregiver_last_name'],
            'caregiver_first_name' => $rows[0]['caregiver_first_name'],
        ];

        // Only add zipcode if BCN.
        if ($this->format === self::BCN) {
            $result['location'] = $rows[0]['location'];
        }

        return $result;
    }

    /**
     * Format the report data for ADP.
     *
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function formatADP(Collection $data) : Collection
    {
        $results = collect([]);

        $data->groupBy(['caregiver_id', 'paycode', 'pay_rate'])
            ->each(function ($cgRow) use ($results) {
                $cgRow->each(function ($payCodeGroup) use ($results) {
                    $payCodeGroup->each(function ($rows) use ($results) {
                        $results->push($this->mapReportRecord($rows));
                    });
                });
            });

        return $results->sortBy('caregiver_last_name')->values();
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
        $results = collect([]);

        $data->groupBy(['caregiver_id', 'location', 'paycode', 'pay_rate'])
            ->each(function ($cgGroup) use ($results) {
                $cgGroup->each(function ($zipGroup) use ($results) {
                    $zipGroup->each(function ($payCodeGroup) use ($results) {
                        $payCodeGroup->each(function ($rows) use ($results) {
                            $results->push($this->mapReportRecord($rows));
                        });
                    });
                });
            });

        return $results->sortBy('caregiver_last_name')->values();
    }

    /**
     * Format the report data to CSV.
     *
     * @return string
     */
    public function toCsv() : string
    {
        $rows = $this->rows()->map(function ($item) {
            return array_merge(Arr::except($item, [
                'caregiver_first_name',
                'caregiver_last_name'
            ]), [
                'hours' => $item['hours'] === '-' ? 'N/A' : number_format($item['hours'], 2),
                'amount' => $item['amount'] === '-' ? 'N/A' : number_format($item['amount'], 2),
                'pay_rate' => $item['pay_rate'] === '-' ? 'N/A' : number_format($item['pay_rate'], 2),
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
        foreach ($rows as $row) {
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
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Payroll-Export-Report.csv"',
        ]);
    }
}
