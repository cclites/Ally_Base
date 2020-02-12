<?php

namespace App\Imports;

use Carbon\Carbon;
use Exception;

class SarasotaImportProcessor extends BaseImportProcessor
{
    /**
     * Return a text based description that summarizes what fields/techniques this import processor uses
     *
     * @return string
     */
    function getDescription()
    {
        return <<<END
The Sarasota format uses the following column headers:

CaregiverLastName
CaregiverFirstName
ClientLastName
ClientFirstName
Date + StartTime (clock in time)
Hours with ModifierType === REG (Regular Hours)
Hours with other ModifierType (OT Hours)
RateOfPay (Caregiver Rate)
TotalBillable / Hours (Provider Fee)
No mileage or other expense calculations are included in this format.

Overtime Multiplier: 1.0 (Not increased)

Any rows without a caregiver name are skipped. (Total Rows)
END;

    }

    /**
     * Do not adjust overtime rates for Sarasota
     *
     * @var float
     */
    public $overTimeMultiplier = 1.0;

    /**
     * Get the caregiver name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getCaregiverName($rowNo)
    {
        return $this->worksheet->getValue('CaregiverLastName', $rowNo) . ', '
            . $this->worksheet->getValue('CaregiverFirstName', $rowNo);
    }

    /**
     * Get the client name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getClientName($rowNo)
    {
        return $this->worksheet->getValue('ClientLastName', $rowNo) . ', '
            . $this->worksheet->getValue('ClientFirstName', $rowNo);
    }

    /**
     * @param $rowNo
     * @param int $offset
     *
     * @return \Carbon\Carbon
     */
    function getStartTime($rowNo, int $offset = 0)
    {
        try {

            $carbon = new Carbon($this->worksheet->getValue('Date', $rowNo), $this->business->timezone);
            $time = $this->worksheet->getValue('StartTime', $rowNo);
            $offset = $offset + (strtotime($time) - strtotime('1970-01-01 00:00:00'));
            return $carbon->addSeconds($offset);
        } catch( \Exception $e ){

            throw new Exception( "Improper Date format detected on Row #" . $rowNo );
        }
    }

    /**
     * Get the number of regular hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getRegularHours($rowNo)
    {
        if ($this->worksheet->getValue('ModifierType', $rowNo) === 'REG') {
            return (float) $this->worksheet->getValue('Hours', $rowNo);
        }
        return 0.0;
    }

    /**
     * Get the number of overtime hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOvertimeHours($rowNo)
    {
        if ($this->worksheet->getValue('ModifierType', $rowNo) !== 'REG') {
            return (float) $this->worksheet->getValue('Hours', $rowNo);
        }
        return 0.0;
    }

    /**
     * Get the caregiver rate from the shift line
     *
     * @param $rowNo
     * @param bool $overtime
     * @return float
     */
    function getCaregiverRate($rowNo, $overtime = false)
    {
        $rate = (float) preg_replace('/[^\d.]/', '', $this->worksheet->getValue('RateOfPay', $rowNo));
        if ($overtime) {
            return round(bcmul($rate, $this->overTimeMultiplier, 4), 2);
        }
        return $rate;
    }

    /**
     * Get the provider fee from the shift line
     *
     * @param $rowNo
     * @param bool $overtime
     * @return float
     */
    function getProviderFee($rowNo, $overtime = false)
    {
        $billTotal = (float) preg_replace('/[^\d.]/', '', $this->worksheet->getValue('TotalBillable', $rowNo));
        $hours = (float) $this->worksheet->getValue('Hours', $rowNo);
        // Divide bill total by total hours to get provider hourly rate

        if( $hours == 0 ) throw new Exception( "Row #" . $rowNo . " has zero hours issue" );

        return round($billTotal / $hours, 2);
    }

    /**
     * Get the number of miles from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getMileage($rowNo)
    {
        return 0.0;
    }

    /**
     * Get the other expenses from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOtherExpenses($rowNo)
    {
        return 0.0;
    }

    /**
     * Determine if the row reflects a valid shift, or if it should be skipped (ex. Summary or Total row)
     *
     * @param $rowNo
     * @return bool
     */
    function skipRow($rowNo)
    {
        return empty(trim($this->worksheet->getValue('CaregiverLastName', $rowNo)));
    }
}
