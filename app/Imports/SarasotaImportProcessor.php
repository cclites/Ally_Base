<?php

namespace App\Imports;

use Carbon\Carbon;

class SarasotaImportProcessor extends BaseImportProcessor
{

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
        $carbon = new Carbon($this->worksheet->getValue('Date', $rowNo), $this->business->timezone);
        $time = $this->worksheet->getValue('StartTime', $rowNo);
        $offset = $offset + (strtotime($time) - strtotime('00:00:00'));
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
        $rate = (float) $this->worksheet->getValue('RateOfPay', $rowNo);
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
        return (float) $this->worksheet->getValue('CostPerUnit', $rowNo);
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
