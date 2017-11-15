<?php
namespace App\Reports;

use App\Client;
use App\Scheduling\AllyFeeCalculator;

class ClientCaregiversReport extends BaseReport
{

    public function __construct()
    {
        $this->query = Client::with('caregivers');
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rows()
    {
        $clients = $this->query()->get();
        $rows = [];
        foreach($clients as $client) {
            $allyPct = AllyFeeCalculator::getPercentage($client, null);
            foreach($client->caregivers as $caregiver) {
                $allyFee = round(($caregiver->pivot->caregiver_hourly_rate + $caregiver->pivot->provider_hourly_fee) * $allyPct, 2);
                $rows[] = [
                    'client_id' => $client->id,
                    'client_name' => $client->nameLastFirst(),
                    'caregiver_id' => $caregiver->id,
                    'caregiver_name' => $caregiver->nameLastFirst(),
                    'caregiver_rate' => $caregiver->pivot->caregiver_hourly_rate,
                    'provider_fee' => $caregiver->pivot->provider_hourly_fee,
                    'ally_fee' => $allyFee,
                    'total_hourly' => $caregiver->pivot->caregiver_hourly_rate + $caregiver->pivot->provider_hourly_fee + $allyFee,
                    'payment_fee' => $allyPct,
                    'payment_type' => $client->getPaymentType(),
                ];
            }
        }
        return collect($rows);
    }
}