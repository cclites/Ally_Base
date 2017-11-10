<?php
namespace App\Reports;

use App\Caregiver;

/**
 * Class CaregiverPaymentsReport
 * Show all pending or completed caregiver payments for a date period
 *
 * @package App\Reports
 */
class CaregiverPaymentsReport extends ScheduledPaymentsReport
{

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        if (!$this->generated) {
            $shifts = $this->query->get();

            foreach($shifts->groupBy('caregiver_id') as $caregiver_id => $caregiver_shifts) {
                $caregiver = Caregiver::find($caregiver_id);
                $row = [
                    'id' => $caregiver_id,
                    'name' => $caregiver->name(),
                    'nameLastFirst' => $caregiver->nameLastFirst(),
                    'hours' => 0,
                    'amount' => 0,
                ];
                foreach($caregiver_shifts as $shift) {
                    /** @var \App\Shift $shift */
                    $row['hours'] += $shift->duration();
                    $row['amount'] += $shift->costs()->getCaregiverCost();
                }
                $this->rows[] = array_map(function($value) {
                    return is_float($value) ? number_format($value, 2) : $value;
                }, $row);
            }
        }
        return $this->rows;
    }

}