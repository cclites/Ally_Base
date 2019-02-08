<?php
namespace App\Reports;

use App\Caregiver;
use App\Billing\GatewayTransaction;
use App\Shift;
use App\Traits\ShiftReportFilters;

/**
 * Class CaregiverPaymentsReport
 * Show all pending or completed caregiver payments for a date period
 *
 * @package App\Reports
 */
class CaregiverPaymentsReport extends ScheduledPaymentsReport
{
    use ShiftReportFilters;

    public function forTransaction(GatewayTransaction $transaction) {
        if ($transaction->payment) {
            $this->query()->whereHas('payment', function($q) use ($transaction) {
                $q->where('payments.id', $transaction->payment->id);
            });
        }
        elseif ($transaction->deposit) {
            $this->query()->whereHas('deposits', function($q) use ($transaction) {
                $q->where('deposits.id', $transaction->deposit->id);
            });
        }
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $shifts = $this->query
            ->whereConfirmed()
            ->get();
        $rows = [];

        foreach ($shifts->groupBy('caregiver_id') as $caregiver_id => $caregiver_shifts) {
            $caregiver = $caregiver_shifts->first()->caregiver;
            $row = [
                'id'            => $caregiver_id,
                'name'          => optional($caregiver)->name(),
                'nameLastFirst' => optional($caregiver)->nameLastFirst(),
                'hours'         => 0,
                'amount'        => 0,
            ];
            foreach ($caregiver_shifts as $shift) {
                /** @var \App\Shift $shift */
                $row['hours'] += $shift->duration();
                $row['amount'] += $shift->costs()->getCaregiverCost();
            }
            $rows[] = array_map(function ($value) {
                return is_float($value) ? number_format($value, 2) : $value;
            }, $row);
        }

        return collect($rows)->sortBy('nameLastFirst')->values();
    }

}