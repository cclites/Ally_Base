<?php

namespace App\Imports;

use App\Business;
use App\Businesses\Timezone;
use App\Caregiver;
use App\Client;
use App\Shift;
use App\Shifts\Data\ClockOutData;
use App\Shifts\ShiftFactory;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;

class ShiftImporter
{

    /**
     * @var \PHPExcel_Worksheet
     */
    private $sheet;

    public function __construct(Worksheet $sheet)
    {
        $this->sheet = $sheet;
    }

    public function import()
    {
        $shifts = [];
        $rowCount = $this->sheet->getRowCount();
        for ($i = 2; $i <= $rowCount; $i++) {
            if ($this->sheet->isRowEmpty($i)) {
                continue;
            }
            $this->validateRow($i);
        }
        for ($i = 2; $i <= $rowCount; $i++) {
            if ($this->sheet->isRowEmpty($i)) {
                continue;
            }
            $shifts[] = $this->importShiftFromRow($i);
        }
        return $shifts;
    }

    public function importShiftFromRow(int $row): Shift
    {
        $factory = $this->getShiftFactory($row);
        return $factory->create();
    }

    public function validateRow(int $row): void
    {
        $businessId = $this->sheet->getValue('business_id', $row);
        $data = $this->getDataFromRow($row);
        if (Carbon::now()->diffInSeconds($data['checked_in_time']) < 10) {
            dd($data['checked_in_time'], $data['checked_out_time']);
            throw new \Exception('The checked_in_time is invalid on row ' . $row . '. Too close to current timestamp.');
        }

        if (!$business = Business::find($businessId)) {
            throw new \Exception('The business ID is invalid on row ' . $row);
        }

        if (!$business->timezone) {
            throw new \Exception('Business ID ' . $business->id . ' does not have a timezone');
        }

        if (!$business->caregivers()->where('caregiver_id', $data['caregiver_id'])->exists()) {
            throw new \Exception('The caregiver ID does not belong to the business on row ' . $row);
        }

        if (!$business->clients()->where('id', $data['client_id'])->exists()) {
            throw new \Exception('The client ID does not belong to the business on row ' . $row);
        }

        if (!in_array($data['hours_type'], ['default', 'overtime', 'holiday'])) {
            throw new \Exception('Invalid hours type on row ' . $row);
        }

        if (!in_array($data['status'], ShiftStatusManager::getAllStatuses())) {
            throw new \Exception('Invalid status on row ' . $row);
        }
    }

    public function getShiftFactory(int $row): ShiftFactory
    {
        // Get client and caregiver records
        $client = Client::findOrFail($this->sheet->getValue('client_id', $row));
        $caregiver = Caregiver::findOrFail($this->sheet->getValue('caregiver_id', $row));

        // Calculate timing
        $businessId = (int) $this->sheet->getValue('business_id', $row);
        $checkIn = $this->sheet->getValue('checked_in_time', $row);
        $timezone = Timezone::getTimezone($businessId);
        $duration = floatval($this->sheet->getValue('duration', $row));
        $caregiverComments = null;

        if ($checkIn) {
            $checkIn = (new Carbon($checkIn, $timezone))->setTimezone('UTC');
            $checkOut = $checkIn->copy()->addMinutes(round($duration * 60));
        }
        else {
            // Allow for an expense only record, set in/out time equal to midnight, 0 duration
            $checkIn = (new Carbon('now', $timezone))->setTime(0,0,0)->setTimezone('UTC');
            $checkOut = $checkIn->copy();
            $caregiverComments = 'Individual expense record imported on ' . (new Carbon('now', $timezone))->format('m/d/Y');
        }

        $clockOutData = new ClockOutData(
            floatval($this->sheet->getValue('mileage', $row)),
            floatval($this->sheet->getValue('other_expenses', $row)),
            null,
            $caregiverComments
        );

        return ShiftFactory::withoutSchedule(
            $client,
            $caregiver,
            $this->sheet->getValue('hours_type', $row) ?? Shift::HOURS_DEFAULT,
            false,
            floatval($this->sheet->getValue('client_rate', $row)),
            floatval($this->sheet->getValue('caregiver_rate', $row)),
            Shift::METHOD_IMPORTED,
            $checkIn,
            Shift::METHOD_IMPORTED,
            $checkOut,
            $this->sheet->getValue('status', $row) ?? Shift::WAITING_FOR_AUTHORIZATION
        )->withData($clockOutData);
    }

    public function getDataFromRow(int $row): array
    {
        return $this->getShiftFactory($row)->toArray();
    }

}
