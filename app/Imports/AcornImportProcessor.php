<?php

namespace App\Imports;

use Carbon\Carbon;
use Exception;

class AcornImportProcessor extends BaseImportProcessor
{

    /**
     * Return a text based description that summarizes what fields/techniques this import processor uses
     *
     * @return string
     */
    function getDescription()
    {
        return <<<END
The Acorn format uses the following column headers:

Caregiver Name (first and last)
Client Name (first and last)
Actual Clock In (clock in time)
Pay Regular Hours (Regular Hours)
Pay OT Hours (OT Hours)
Payroll Rate (Caregiver Rate)
Bill Total / Total Hours (Provider Fee)
Mileage in dollar amounts (Divided by registry mileage_rate to get Mileage)
Expenses in dollar amounts (Other Expenses)

Overtime Multiplier: 1.5 (Default)
END;

    }

    /**
     * Get the caregiver name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getCaregiverName($rowNo)
    {
        return $this->worksheet->getValue('Caregiver Name', $rowNo);
    }

    /**
     * Get the client name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getClientName($rowNo)
    {
        return $this->worksheet->getValue('Client Name', $rowNo);
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

            $carbon = new Carbon($this->worksheet->getValue('Actual Clock In', $rowNo), $this->business->timezone);
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
        return (float) $this->worksheet->getValue('Pay Regular Hours', $rowNo);
    }

    /**
     * Get the number of overtime hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOvertimeHours($rowNo)
    {
        return (float) $this->worksheet->getValue('Pay OT Hours', $rowNo);
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
        $rate = (float) $this->worksheet->getValue('Payroll Rate', $rowNo);
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
        // Get evaluated Bill Total column
        $billTotal = (float) $this->worksheet->getValue('Bill Total', $rowNo, true);
        // Divide bill total by total hours to get provider hourly rate
        if( ( $this->getRegularHours($rowNo) + $this->getOvertimeHours($rowNo) ) == 0 ) throw new Exception( "Row #" . $rowNo . " has zero hours issue" );

        return round($billTotal / ($this->getRegularHours($rowNo) + $this->getOvertimeHours($rowNo)), 2);
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

        if( $mileageRate == 0 ) throw new Exception( "Business " . $this->business->name . " has zero mileage rate, caught on Row #" . $rowNo );

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
        return (float) $this->worksheet->getValue('Expenses', $rowNo);
    }

    /**
     * Determine if the row reflects a valid shift, or if it should be skipped (ex. Summary or Total row)
     *
     * @param $rowNo
     * @return bool
     */
    function skipRow($rowNo)
    {
        return empty(trim($this->worksheet->getValue('Client Name', $rowNo)));
    }
}
