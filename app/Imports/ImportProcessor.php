<?php

namespace App\Imports;

use App\Business;

interface ImportProcessor
{
    /**
     * ImportProcessor constructor.
     *
     * @param \App\Business $business
     * @param string $file
     */
    function __construct(Business $business, string $file);

    /**
     * Handle the import
     *
     * @return \Illuminate\Support\Collection
     */
    function handle();

    /**
     * Get the caregiver name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getCaregiverName($rowNo);

    /**
     * Get the client name in a "Last, First" format
     *
     * @param $rowNo
     * @return string
     */
    function getClientName($rowNo);

    /**
     * @param $rowNo
     * @param int $offset
     *
     * @return \Carbon\Carbon
     */
    function getStartTime($rowNo, int $offset = 0);

    /**
     * Get the number of regular hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getRegularHours($rowNo);

    /**
     * Get the number of overtime hours from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOvertimeHours($rowNo);

    /**
     * Get the caregiver rate from the shift line
     *
     * @param $rowNo
     * @param bool $overtime
     * @return float
     */
    function getCaregiverRate($rowNo, $overtime = false);

    /**
     * Get the provider fee from the shift line
     *
     * @param $rowNo
     * @param bool $overtime
     * @return float
     */
    function getProviderFee($rowNo, $overtime = false);

    /**
     * Get the number of miles from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getMileage($rowNo);

    /**
     * Get the other expenses from the shift line
     *
     * @param $rowNo
     * @return float
     */
    function getOtherExpenses($rowNo);

    /**
     * Find a caregiver record based on the name
     *
     * @param string $name
     * @return \App\Caregiver|null
     */
    function findCaregiver($name);

    /**
     * Find a client record based on the name
     *
     * @param string $name
     * @return \App\Client|null
     */
    function findClient($name);
}
