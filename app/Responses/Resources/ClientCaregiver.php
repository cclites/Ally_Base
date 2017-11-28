<?php


namespace App\Responses\Resources;


use App\Scheduling\AllyFeeCalculator;
use Illuminate\Contracts\Support\Responsable;

class ClientCaregiver implements Responsable
{
    protected $client;
    protected $caregiver;

    public function __construct($client, $caregiver)
    {
        $this->client = $client;
        $this->caregiver = $caregiver;
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

        // Add fee calculations to pivot object
        if ($caregiver->pivot) {
            $caregiver->pivot->ally_hourly_fee = number_format(AllyFeeCalculator::getHourlyRate($client, $client->getPaymentMethod(),
                $caregiver->pivot->caregiver_hourly_rate, $caregiver->pivot->provider_hourly_fee), 2);
            $caregiver->pivot->ally_daily_fee = number_format(AllyFeeCalculator::getFee($client, $client->getPaymentMethod(),
                $caregiver->pivot->caregiver_daily_rate + $caregiver->pivot->provider_daily_fee), 2);
            $caregiver->pivot->total_hourly_fee = number_format(
                round($caregiver->pivot->caregiver_hourly_rate + $caregiver->pivot->provider_hourly_fee + $caregiver->pivot->ally_hourly_fee, 2),
                2);
            $caregiver->pivot->total_daily_fee = number_format(
                round($caregiver->pivot->caregiver_daily_rate + $caregiver->pivot->provider_daily_fee + $caregiver->pivot->ally_daily_fee, 2),
                2);
        }

        return $caregiver;
    }
}