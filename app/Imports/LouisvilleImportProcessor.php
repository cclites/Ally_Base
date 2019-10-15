<?php

namespace App\Imports;

use Carbon\Carbon;

class LouisvilleImportProcessor extends BaseImportProcessor
{
    /**
     * Return a text based description that summarizes what fields/techniques this import processor uses
     *
     * @return string
     */
    function getDescription()
    {
        return <<<END
The Louisville format uses the following column headers:

CaregiverLast
CaregiverFirst
ClientLast
ClientFirst
Date + StartTime (clock in time)
HoursWorked (Regular Hours)
Over Time Hrs (OT Hours)
CaregiverRate, if the row has overtime hours, divide by the overtime multiplier for the regular hour rate (Caregiver Rate)
ReferralRate / Hours (Provider Fee)
Mileage in dollar amounts (Divided by registry mileage_rate to get Mileage)
No other expense calculations are included in this format.

Overtime Multiplier: 1.5 (default but only used in reverse to calculate regular hour rate on combined rows)
END;

    }

    /**
     * Do not adjust overtime rates for Sarasota
     *
     * @var float
     */
    public $overTimeMultiplier = 1.5;

    /**
     * Get the caregiver name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getCaregiverName($rowNo)
    {
        return $this->worksheet->getValue('CaregiverLast', $rowNo) . ', '
            . $this->worksheet->getValue('CaregiverFirst', $rowNo);
    }

    /**
     * Get the client name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getClientName($rowNo)
    {
        return $this->worksheet->getValue('ClientLast', $rowNo) . ', '
            . $this->worksheet->getValue('ClientFirst', $rowNo);
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
            $offset = $offset + (strtotime($time) - strtotime('00:00:00'));
            return $carbon->addSeconds($offset);
        } catch( \Exception $e ){

            throw new ErrorException( "Improper Date format detected on Row #" . $rowNo );
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
        return (float) $this->worksheet->getValue('HoursWorked', $rowNo);
    }

    /**
     * Get the number of overtime hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOvertimeHours($rowNo)
    {
        return (float) $this->worksheet->getValue('Over Time Hrs', $rowNo);
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
        $rate = (float) preg_replace('/[^\d.]/', '', $this->worksheet->getValue('CaregiverRate', $rowNo));
        // Reverse the overtime multiplier for Louisville (Overtime rate is given in worksheet for combined rows)
        if (!$overtime && $this->getOvertimeHours($rowNo) > 0) {
            return round(bcdiv($rate, $this->overTimeMultiplier, 4), 2);
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
        $billTotal = (float) preg_replace('/[^\d.]/', '', $this->worksheet->getValue('ReferralRate', $rowNo));
        $hours = (float) $this->getRegularHours($rowNo) + $this->getOvertimeHours($rowNo);
        // Divide bill total by total hours to get provider hourly rate

        if( $hours == 0 ) throw new ErrorException( "Row #" . $rowNo . " has zero hours issue" );

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
        $mileageAmount = $this->worksheet->getValue('Mileage', $rowNo);
        $mileageRate = $this->business->mileage_rate;

        if( $mileageRate == 0 ) throw new ErrorException( "Business " . $this->business->name . " has zero mileage rate, caught on Row #" . $rowNo );

        return round(
            bcdiv($mileageAmount, $mileageRate, 4),
            2
        );
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
        return empty(trim($this->worksheet->getValue('ClientLast', $rowNo)));
    }
}
