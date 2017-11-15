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
        $rows = $clients->map(function($client) {
            $allyPct = AllyFeeCalculator::getPercentage($client, null);
            return [
                'client_id' => $client->id,
                'client_name' => $client->nameLastFirst(),
                'caregiver_id' => $client->caregiver->id,
                'caregiver_name' => $client->caregiver->nameLastFirst(),
                'caregiver_rate' => $client->caregiver->pivot->caregiver_rate,
                'provider_fee' => $client->caregiver->pivot->provider_fee,
                'ally_fee' => round(($client->caregiver->pivot->caregiver_rate + $client->caregiver->pivot->provider_fee) * $allyPct),
                'ally_percentage' => $allyPct,
                'payment_type' => $client->getPaymentType(),
            ];
        });
        return $rows;
    }
}