<?php

namespace App\Responses\Resources;

use App\Shifts\AllyFeeCalculator;
use App\Shifts\RateFactory;
use Illuminate\Contracts\Support\Responsable;
use App\Shift;

class ClientCaregiver implements Responsable
{
    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * @var \App\Caregiver
     */
    protected $caregiver;

    /**
     * @var array|null
     */
    protected $pivot;

    public function __construct($client, $caregiver)
    {
        $this->client = $client;
        $this->caregiver = $caregiver;

        $pivot = $caregiver->pivot ?? $client->pivot ?? null;
        if ($pivot) $this->pivot = $pivot->toArray();
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $caregiver = $this->caregiver;
        $client = $this->client;

        // Add names
        $caregiver->name = $caregiver->nameLastFirst();
        $caregiver->firstname = $caregiver->user->firstname;
        $caregiver->lastname = $caregiver->user->lastname;

        $caregiver->rates = [
            'hourly' => app(RateFactory::class)->getRatesForClientCaregiver($client, $caregiver, false, $this->pivot),
            'fixed' => app(RateFactory::class)->getRatesForClientCaregiver($client, $caregiver, true, $this->pivot),
        ];

        // TODO: Remove/remove all dependencies for changes to pivot, only kept for backwards compatibility
        // TODO: Pivot should only contain fields from database
        // Add fee calculations to pivot object
        if ($caregiver->pivot) {
            $caregiver->pivot->ally_hourly_fee = number_format(AllyFeeCalculator::getHourlyRate(
                $client,
                $client->getPaymentMethod(),
                $caregiver->pivot->caregiver_hourly_rate,
                $caregiver->pivot->provider_hourly_fee
            ), 2);
            $caregiver->pivot->ally_daily_fee = number_format(AllyFeeCalculator::getFee(
                $client,
                $client->getPaymentMethod(),
                $caregiver->pivot->caregiver_fixed_rate + $caregiver->pivot->provider_fixed_fee
            ), 2);
            $caregiver->pivot->total_hourly_fee = number_format(
                round($caregiver->pivot->caregiver_hourly_rate + $caregiver->pivot->provider_hourly_fee + $caregiver->pivot->ally_hourly_fee, 2),
                2
            );
            $caregiver->pivot->total_daily_fee = number_format(
                round($caregiver->pivot->caregiver_fixed_rate + $caregiver->pivot->provider_fixed_fee + $caregiver->pivot->ally_daily_fee, 2),
                2
            );
        }

        $caregiver->last_service_date = $caregiver->getLastServiceDate($client);
        $caregiver->total_hours = $caregiver->getTotalClockedHours($client);
        
        return $caregiver;
    }
}
