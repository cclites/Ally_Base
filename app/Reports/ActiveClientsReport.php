<?php
namespace App\Reports;

use App\Shift;
use App\Billing\Payment;

class ActiveClientsReport extends BusinessResourceReport
{
    public $range;
    public $compareRange;

    public function __construct($business_id, $range, $compareRange)
    {
        $this->range = $range;
        $this->compareRange = $compareRange;

        if (!empty($business_id)) {
            $this->query = Shift::where('business_id', $business_id);
        } else {
            $this->query = Shift::query();
        }
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
    protected function results()
    {
        $report1 = $this->getClientReportForRange($this->range);
        $report2 = $this->getClientReportForRange($this->compareRange);
        $report3 = $this->compareClientReports($report1, $report2);
        return collect(compact(['report1', 'report2', 'report3']));
    }

    /**
     * Gets the shfit query for the given date range.
     *
     * @param [type] $range
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function queryForRange($range) 
    {
        $query = clone $this->query();
        return $query->whereBetween('checked_in_time', $range);
    }

    /**
     * Gets the number of active clients in the given date range.
     *
     * @param [type] $range
     * @return int
     */
    public function activeClientsCount($range) 
    {
        return $this->queryForRange($range)
            ->select('client_id')
            ->distinct()
            ->get()
            ->count();
    }

    /**
     * Gets the number of active caregivers in the given date range.
     *
     * @param [type] $range
     * @return int
     */
    public function activeCaregiversCount($range) 
    {
        return $this->queryForRange($range)
            ->select('caregiver_id')
            ->distinct()
            ->get()
            ->count();
    }

    /**
     * Gets the number of shifts in the given date range.
     *
     * @param [type] $range
     * @return int
     */
    public function totalShifts($range) 
    {
        return $this->queryForRange($range)->count();
    }

    /**
     * Gets the total number of hours in every shift
     * in the given date range.
     *
     * @param [type] $range
     * @return int
     */
    public function totalHours($range) 
    {
        return $this->queryForRange($range)
            ->select(\DB::raw('COALESCE(SUM(TIMESTAMPDIFF(hour, checked_in_time, checked_out_time)), 0) as total_hours'))
            ->first()
            ->total_hours;
    }

    /**
     * Gets the total amount of payments for every shift 
     * in the given date range.
     *
     * @param [type] $range
     * @return float
     */
    public function totalCharges($range)
    {
        return Payment::whereIn('id', $this->queryForRange($range)
            ->pluck('payment_id')) 
            ->sum('amount');
    }

    /**
     * Gets the number of shifts verified in the given date range.
     *
     * @param [type] $range
     * @return float
     */
    public function verifiedShifts($range) 
    {
        return $this->queryForRange($range)
            ->where('verified', 1)
            ->count();
    }

    /**
     * Gets the percent of shifts verified by tele during
     * the given date range.
     *
     * @param [type] $range
     * @param [type] $total
     * @return float
     */
    public function telephonyPercent($range, $total) 
    {
        $verified = $this->queryForRange($range)
            ->whereTelephonyVerified()
            ->count();

        return $this->percent($verified, $total);
    }

    /**
     * Gets the percent of shifts verified by mobile during
     * the given date range.
     *
     * @param [type] $range
     * @param [type] $total
     * @return float
     */
    public function mobilePercent($range, $total) 
    {
        $verified = $this->queryForRange($range)
            ->whereMobileVerified()
            ->count();

        return $this->percent($verified, $total);
    }

    /**
     * Gets the active client report data for a given date range.
     *
     * @param [type] $range
     * @return array
     */
    public function getClientReportForRange($range) 
    {
        $totalShifts = $this->totalShifts($range);
        $verified = $this->verifiedShifts($range);

        return [
            'active_clients' => number_format($this->activeClientsCount($range), 0),
            'active_caregivers' => number_format($this->activeCaregiversCount($range), 0),
            'total_shifts' => number_format($totalShifts, 0),
            'total_hours_billed' => number_format($this->totalHours($range), 0),
            'total_charges' => number_format($this->totalCharges($range), 2),
            'verified_shifts' => number_format($this->percent($verified, $totalShifts), 0),
            'telephony' => number_format($this->telephonyPercent($range, $verified), 0),
            'mobile_app' => number_format($this->mobilePercent($range, $verified), 0),
        ];
    }
    
    /**
     * Compares two active client reports and returns the comparison.
     *
     * @param [type] $report1
     * @param [type] $report2
     * @return array
     */
    public function compareClientReports($report1, $report2) 
    {
        $data = [];

        foreach($report1 as $key => $val) {
            $data["{$key}_diff"] = $this->subtract($report2[$key], $report1[$key]);
            $data["{$key}_percent"] = number_format($this->percent($data["{$key}_diff"], $report1[$key]), 0);
        }

        $data['total_charges_diff'] = number_format($data['total_charges_diff'], 2);
        
        return $data;
    }

    /**
     * Helper function to calculate percent and ignore division by zero
     * errors as well as fixing number formatting.
     *
     * @param [type] $part
     * @param [type] $whole
     * @return float
     */
    public function percent($part, $whole) {
        $part = floatval(str_replace(',', '', $part));
        $whole = floatval(str_replace(',', '', $whole));
        
        if ($whole == 0) return 0;

        return ($part / $whole) * 100;
    }

    /**
     * Helper function to subtract two floats and fix number formatting.
     *
     * @param [type] $part
     * @param [type] $whole
     * @return float
     */
    public function subtract($part, $whole) {
        $part = floatval(str_replace(',', '', $part));
        $whole = floatval(str_replace(',', '', $whole));

        return $part - $whole;
    }
}