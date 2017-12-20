<?php
namespace App\Reports;

use App\Client;
use App\Shift;

/**
 * Class ClientChargesReport
 * Show all pending or completed client charges for a date period
 *
 * @package App\Reports
 */
class ClientChargesReport extends ScheduledPaymentsReport
{

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        if (!$this->generated) {
            $shifts = $this->query
                ->where('status', '!=', Shift::UNCONFIRMED)
                ->get();
            $this->rows = [];

            foreach ($shifts->groupBy('client_id') as $client_id => $client_shifts) {
                $client = Client::find($client_id);
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
                    $row['hours'] += $shift->duration();
                    $row['caregiver_total'] += $shift->costs()->getCaregiverCost();
                    $row['provider_total'] += $shift->costs()->getProviderFee();
                    $row['ally_total'] += $shift->costs()->getAllyFee();
                    $row['total'] += $shift->costs()->getTotalCost();
                }
                $this->rows[] = array_map(function ($value) {
                    return is_float($value) ? number_format($value, 2) : $value;
                }, $row);
            }

            // Sort by name
            usort($this->rows, function ($a, $b) {
                return strcmp($a['nameLastFirst'], $b['nameLastFirst']);
            });
        }
        return $this->rows;
    }
}