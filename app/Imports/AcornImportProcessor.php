<?php

namespace App\Imports;

use Carbon\Carbon;

class AcornImportProcessor extends BaseImportProcessor
{

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
        $carbon = new Carbon($this->worksheet->getValue('Actual Clock In', $rowNo), $this->business->timezone);
        return $carbon->addSeconds($offset);
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
            return bcmul($rate, $this->overTimeMultiplier, 2);
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
        $billTotal = (float) $this->worksheet->getValue('Bill Total', $rowNo);
        // Divide bill total by total hours to get provider hourly rate
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

}
