<?php

namespace App\Imports;

use App\Business;
use App\Shift;
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

    public function importShiftFromRow(int $row)
    {
        $data = $this->getDataFromRow($row);
        return Shift::create($data);
    }

    public function validateRow(int $row)
    {
        $data = $this->getDataFromRow($row);
        if (Carbon::now()->diffInSeconds($data['checked_in_time']) < 10) {
            dd($data['checked_in_time'], $data['checked_out_time']);
            throw new \Exception('The checked_in_time is invalid on row ' . $row . '. Too close to current timestamp.');
        }

        if (!$business = Business::find($data['business_id'])) {
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

    public function getDataFromRow(int $row)
    {
        $data = [
            'business_id'    => $this->sheet->getValue('business_id', $row),
            'client_id'      => $this->sheet->getValue('client_id', $row),
            'caregiver_id'   => $this->sheet->getValue('caregiver_id', $row),
            'caregiver_rate' => floatval($this->sheet->getValue('caregiver_rate', $row)),
            'provider_fee'   => floatval($this->sheet->getValue('provider_fee', $row)),
            'hours_type'     => $this->sheet->getValue('hours_type', $row) ?? 'default',
            'mileage'        => floatval($this->sheet->getValue('mileage', $row)),
            'other_expenses' => floatval($this->sheet->getValue('other_expenses', $row)),
            'status'         => $this->sheet->getValue('status', $row) ?? Shift::WAITING_FOR_AUTHORIZATION,
        ];

        // Calculate timing
        $checkIn = $this->sheet->getValue('checked_in_time', $row);
        $timezone = Business::find($data['business_id'])->timezone;
        $duration = floatval($this->sheet->getValue('duration', $row));
        $data['checked_in_time'] = (new Carbon($checkIn, $timezone))->setTimezone('UTC');
        $data['checked_out_time'] = $data['checked_in_time']->copy()->addMinutes(round($duration * 60));

        return $data;
    }

}