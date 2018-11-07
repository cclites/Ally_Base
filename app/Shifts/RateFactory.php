<?php
namespace App\Shifts;

use App\Businesses\Settings;
use App\Caregiver;
use App\Client;
use App\Contracts\HasAllyFeeInterface;
use App\Schedule;
use App\Shift;

class RateFactory
{

    /**
     * @var \App\Businesses\Settings
     */
    protected $settings;


    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    ////////////////////////////////////
    //// TODO : Need a method to get the Charged Rate (using Client Rate or Caregiver Rate + Provider Fee)
    ////////////////////////////////////

    function getAllyFee(HasAllyFeeInterface $paymentMethod, float $chargedRate)
    {
        return (float) $paymentMethod->getAllyFee($chargedRate);
    }

    function getProviderFee(float $clientRate, float $caregiverRate, float $allyFee)
    {
        return (float) bcsub(
            bcsub($clientRate, $caregiverRate, 2),
            $allyFee,
            2
        );
    }

    function getClientRate(float $providerFee, float $caregiverRate, float $allyFee)
    {
        return (float) bcadd(
            bcadd($providerFee, $caregiverRate, 2),
            $allyFee,
            2
        );
    }


    ////////////////////////////////////
    //// TODO: Re-work these using the above granular methods
    ////////////////////////////////////

    /**
     * @param \App\Shift $shift
     * @return \App\Shifts\Rates
     */
    public function getRatesForShift(Shift $shift)
    {
        if ($shift->schedule) {
            return $this->getRatesForSchedule($shift->schedule);
        }

        return $this->getRatesForClientCaregiver($shift->client_id, $shift->caregiver_id, false);
    }

    /**
     * @param \App\Schedule $schedule
     * @return \App\Shifts\Rates
     */
    public function getRatesForSchedule(Schedule $schedule)
    {
        return new Rates($schedule->caregiver_rate, $schedule->provider_fee, $schedule->fixed_rates);
    }

    /**
     * @param \App\Client $client
     * @param \App\Caregiver $caregiver
     * @param bool $fixedRates
     * @return \App\Shifts\Rates
     */
    public function getRatesForClientCaregiver(Client $client, Caregiver $caregiver, $fixedRates = false)
    {
        $clientCaregiverRow = \DB::table('client_caregivers')->where('client_id', $client->id)->where('caregiver_id', $caregiver->id)->first();

        $caregiver_rate = $this->getCaregiverRateFromRow($clientCaregiverRow, $fixedRates)
            ?? $this->getDefaultCaregiverRate($caregiver, $fixedRates);
        $provider_fee = $this->getProviderFeeFromRow($clientCaregiverRow, $fixedRates);
        $client_rate = ($provider_fee === null) ? $this->getDefaultClientRate($client, $fixedRates) : null;

        $rates = new Rates($caregiver_rate, $provider_fee ?? 0.0, $fixedRates);
        if ($client_rate) {
            $rates->setClientRate($client_rate);
        }
        return $rates;
    }

    /**
     * @param \App\Client $client
     * @param bool $fixedRates
     * @return float
     */
    public function getDefaultClientRate(Client $client, $fixedRates = false)
    {
        if ($fixedRates) {
            return $client->defaultFixedRate ? $client->defaultFixedRate->rate : 0.0;
        }

        return $client->defaultHourlyRate ? $client->defaultHourlyRate->rate : 0.0;
    }

    /**
     * @param \App\Caregiver $caregiver
     * @param bool $fixedRates
     * @return float
     */
    public function getDefaultCaregiverRate(Caregiver $caregiver, $fixedRates = false)
    {
        if ($fixedRates) {
            return $caregiver->defaultFixedRate ? $caregiver->defaultFixedRate->rate : 0.0;
        }

        return $caregiver->defaultHourlyRate ? $caregiver->defaultHourlyRate->rate : 0.0;
    }

    protected function getCaregiverRateFromRow($row, $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';
        return $row->{"caregiver_${fieldId}_rate"} ?? null;
    }

    protected function getProviderFeeFromRow($row, $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';
        return $row->{"provider_${fieldId}_fee"} ?? null;
    }
}
