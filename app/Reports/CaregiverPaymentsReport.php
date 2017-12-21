<?php
namespace App\Reports;

use App\Caregiver;
use App\GatewayTransaction;
use App\Shift;

/**
 * Class CaregiverPaymentsReport
 * Show all pending or completed caregiver payments for a date period
 *
 * @package App\Reports
 */
class CaregiverPaymentsReport extends ScheduledPaymentsReport
{

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
            ->where('status', '!=', Shift::UNCONFIRMED)
            ->get();
        $rows = [];

        foreach ($shifts->groupBy('caregiver_id') as $caregiver_id => $caregiver_shifts) {
            $caregiver = Caregiver::find($caregiver_id);
            $row = [
                'id'            => $caregiver_id,
                'name'          => $caregiver->name(),
                'nameLastFirst' => $caregiver->nameLastFirst(),
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