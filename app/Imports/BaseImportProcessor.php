<?php

namespace App\Imports;

use App\Business;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Shift;

abstract class BaseImportProcessor implements ImportProcessor
{

    /**
     * @var float
     */
    public $overTimeMultiplier = 1.5;

    /**
     * @var \App\Imports\Worksheet
     */
    public $worksheet;

    /**
     * @var \App\Business
     */
    public $business;

    /**
     * ImportProcessor constructor.
     *
     * @param \App\Business $business
     * @param string $file
     */
    function __construct(Business $business, string $file)
    {
        $this->business = $business;
        $this->worksheet = new Worksheet($file);
    }

    /**
     * Handle the import
     *
     * @return \Illuminate\Support\Collection
     */
    function handle()
    {
        $collection = collect();

        for($i=2, $n=$this->worksheet->getRowCount(); $i<$n; $i++) {
            $shift = null;
            if ($this->getRegularHours($i) > 0) {
                $shift = $this->addRegularShift($collection, $i);
            }
            if ($this->getOvertimeHours($i) > 0) {
                $this->addOvertimeShift($collection, $i, $shift);
            }
        }

        return $collection;
    }

    /**
     * Find a caregiver record based on the name
     *
     * @param string $name
     * @return \App\Caregiver|null
     */
    function findCaregiver($name)
    {
        // Search by import_identifier
        if ($caregiver = $this->business->caregivers()->where('import_identifier', $name)->first()) {
            return $caregiver;
        }

        // Search by exact name, if one match
        $caregivers = $this->business->caregivers()->whereHas('user', function($q) use ($name) {
            $q->whereRaw('CONCAT(lastname, ", ", firstname) = ?', [trim($name)]);
        })->get();
        if ($caregivers->count() === 1) {
            return $caregivers->first();
        }

        return null;
    }

    /**
     * Find a client record based on the name
     *
     * @param string $name
     * @return \App\Client|null
     */
    function findClient($name)
    {
        // Search by import_identifier
        if ($client = $this->business->clients()->where('import_identifier', $name)->first()) {
            return $client;
        }

        // Search by exact name, if one match
        $clients = $this->business->clients()->whereHas('user', function($q) use ($name) {
            $q->whereRaw('CONCAT(lastname, ", ", firstname) = ?', [trim($name)]);
        })->get();
        if ($clients->count() === 1) {
            return $clients->first();
        }

        return null;
    }

    /**
     * Add a regular hours shift
     *
     * @param \Illuminate\Support\Collection $collection
     * @param $rowNo
     * @return \App\Imports\Shift
     */
    function addRegularShift(Collection $collection, $rowNo)
    {
        $checkIn = $this->getStartTime($rowNo)->setTimezone('UTC');
        $hours = $this->getRegularHours($rowNo);

        return $this->_addShift($collection, $rowNo, $checkIn, $hours, false);
    }

    /**
     * Add an overtime shift
     *
     * @param \Illuminate\Support\Collection $collection
     * @param $rowNo
     * @param \App\Shift|null $shift
     * @return \App\Shift
     */
    function addOvertimeShift(Collection $collection, $rowNo, Shift $shift = null)
    {
        if ($shift) {
            $checkIn = new Carbon($shift->checked_out_time, 'UTC');
        }
        else {
            $checkIn = $this->getStartTime($rowNo)->setTimezone('UTC');
        }
        $hours = $this->getOvertimeHours($rowNo);

        return $this->_addShift($collection, $rowNo, $checkIn, $hours, true);
    }

    /**
     * Add a shift (used by addRegularShift and addOvertimeShift)
     *
     * @param \Illuminate\Support\Collection $collection
     * @param $rowNo
     * @param \Carbon\Carbon $checkIn
     * @param $hours
     * @param bool $overtime
     * @return \App\Shift
     */
    function _addShift(Collection $collection, $rowNo, Carbon $checkIn, $hours, $overtime = false)
    {
        $caregiverName = $this->getCaregiverName($rowNo);
        $clientName = $this->getClientName($rowNo);
        $checkOut = $checkIn->copy()->addSeconds(round($hours * 3600));

        $shift = new Shift([
            'business_id' => $this->business->id,
            'caregiver_id' => ($caregiver = $this->findCaregiver($caregiverName)) ? $caregiver->id : null,
            'client_id' => ($client = $this->findClient($clientName)) ? $client->id : null,
            'checked_in_time' => $checkIn->toDateTimeString(),
            'checked_out_time' => $checkOut->toDateTimeString(),
            'caregiver_rate' => $this->getCaregiverRate($rowNo, $overtime),
            'provider_fee' => $this->getProviderFee($rowNo, $overtime),
            'mileage' => $this->getMileage($rowNo),
            'other_expenses' => $this->getOtherExpenses($rowNo),
            'hours_type' => ($overtime) ? 'overtime' : 'default',
        ]);

        $array = [
            'shift' => $shift,
            'identifiers' => [
                'caregiver_name' => $caregiverName,
                'client_name' => $clientName,
            ]
        ];

        $collection->push($array);
        return $shift;
    }
}
