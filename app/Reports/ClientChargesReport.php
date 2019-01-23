<?php
namespace App\Reports;

use App\Client;
use App\Billing\GatewayTransaction;
use App\Shift;
use App\Traits\ShiftReportFilters;

/**
 * Class ClientChargesReport
 * Show all pending or completed client charges for a date period
 *
 * @package App\Reports
 */
class ClientChargesReport extends ScheduledPaymentsReport
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

        foreach ($shifts->groupBy('client_id') as $client_id => $client_shifts) {
            $client = $client_shifts->first()->client;
            $row = [
                'id'              => $client_id,
                'name'            => $client->name(),
                'nameLastFirst'   => $client->nameLastFirst(),
                'payment_type'    => $client->getPaymentType(),
                'hours'           => 0,
                'caregiver_total' => 0,
                'provider_total'  => 0,
                'ally_total'      => 0,
                'total'           => 0,
            ];
            foreach ($client_shifts as $shift) {
                /** @var \App\Shift $shift */
                $row['hours'] += floatval($shift->duration());
                $row['caregiver_total'] += $shift->costs()->getCaregiverCost();
                $row['provider_total'] += $shift->costs()->getProviderFee();
                $row['ally_total'] += $shift->costs()->getAllyFee();
                $row['total'] += $shift->costs()->getTotalCost();
            }
            $rows[] = array_map(function ($value) {
                return is_float($value) ? number_format($value, 2) : $value;
            }, $row);
        }

        // Sort by name
        usort($rows, function ($a, $b) {
            return strcmp($a['nameLastFirst'], $b['nameLastFirst']);
        });

        return collect($rows);
    }
}