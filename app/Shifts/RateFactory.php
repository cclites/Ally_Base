<?php
namespace App\Shifts;

use App\Businesses\Settings;
use App\Caregiver;
use App\Client;
use App\Contracts\HasAllyFeeInterface;
use App\CreditCard;
use App\RateCode;
use App\Schedule;
use App\Shift;
use http\Exception\InvalidArgumentException;

class RateFactory
{

    /**
     * @var \App\Businesses\Settings
     */
    protected $settings;

    /**
     * RateFactory constructor.
     * @param \App\Businesses\Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get the rate structure setting of a given business
     *
     * @param int $businessId
     * @return mixed
     */
    function getRateStructure(int $businessId)
    {
        return $this->settings->get($businessId, 'rate_structure', 'provider_fee');
    }

    /**
     * Determine ally fee included setting of a given business
     *
     * @param int $businessId
     * @return bool
     */
    function allyFeeIncluded(int $businessId)
    {
        return $this->getRateStructure($businessId) === 'client_rate'
            && $this->settings->get($businessId, 'include_ally_fee', false);
    }

    /**
     * Determine the effective charged rate, used to calculate the Ally Fee
     *
     * @param int $businessId
     * @param float $caregiverRate
     * @param float|null $providerFee
     * @param float|null $clientRate
     * @return float
     */
    function getChargedRate(int $businessId, float $caregiverRate, float $providerFee = null, float $clientRate = null)
    {
        $rateStructure = $this->getRateStructure($businessId);

        if ($rateStructure === 'client_rate' && $clientRate > $caregiverRate) {
            return (float) $clientRate;
        }

        return (float) bcadd($providerFee, $caregiverRate, 2);
    }

    /**
     * Get the ally fee based on the payment method and effective charged rate
     *
     * @param \App\Contracts\HasAllyFeeInterface $paymentMethod
     * @param float $chargedRate
     * @return float
     */
    function getAllyFee(HasAllyFeeInterface $paymentMethod, float $chargedRate)
    {
        return (float) $paymentMethod->getAllyFee($chargedRate);
    }

    /**
     * Calculate the provider fee with the given information
     *
     * @param float $clientRate
     * @param float $caregiverRate
     * @param float $allyFee
     * @param bool $allyFeeIncluded
     * @return float
     */
    function getProviderFee(float $clientRate, float $caregiverRate, float $allyFee, bool $allyFeeIncluded = false)
    {
        return (float) bcsub(
            bcsub($clientRate, $caregiverRate, 2),
            $allyFeeIncluded ? $allyFee : "0",
            2
        );
    }

    /**
     * Calculate the client rate with the given information
     *
     * @param float $providerFee
     * @param float $caregiverRate
     * @return float
     */
    function getClientRate(float $providerFee, float $caregiverRate)
    {
        return (float) bcadd($providerFee, $caregiverRate, 2);
    }

    /**
     * Build and return the Rates object
     *
     * @param \App\Contracts\HasAllyFeeInterface $paymentMethod
     * @param int $businessId
     * @param bool $fixedRates
     * @param float $caregiverRate
     * @param float|null $providerFee
     * @param float|null $clientRate
     * @return \App\Shifts\Rates
     */
    function getRateObject(HasAllyFeeInterface $paymentMethod, int $businessId, bool $fixedRates, float $caregiverRate, float $providerFee = null, float $clientRate = null)
    {
        $chargedRate = $this->getChargedRate($businessId, $caregiverRate, $providerFee, $clientRate);
        $allyFee = $this->getAllyFee($paymentMethod, $chargedRate);
        $rateStructure = $this->getRateStructure($businessId);
        $allyFeeIncluded = $this->allyFeeIncluded($businessId);

        if ($rateStructure === 'client_rate') {
            $clientRate = $this->getClientRate(0.0, $chargedRate);
            $providerFee = $this->getProviderFee($clientRate, $caregiverRate, $allyFee, $allyFeeIncluded);
        }
        else {
            $clientRate = $this->getClientRate($providerFee, $caregiverRate);
        }

        return new Rates(
            $caregiverRate,
            $providerFee,
            $clientRate,
            $allyFee,
            $allyFeeIncluded,
            $fixedRates
        );
    }

    /**
     * @param \App\Shift $shift
     * @return \App\Shifts\Rates
     * TODO IMPORTANT:  RATE PERSISTENCE FOR CHARGED SHIFTS
     */
    public function getRatesForShift(Shift $shift)
    {
        // TODO IMPORTANT:  RATE PERSISTENCE FOR CHARGED SHIFTS

        if ($shift->schedule) {
            return $this->getRatesForSchedule($shift->schedule);
        }

        // Assume hourly and pull data from client caregiver relationship
        return $this->getRatesForClientCaregiver($shift->client_id, $shift->caregiver_id, false);
    }

    /**
     * @param \App\Schedule $schedule
     * @return \App\Shifts\Rates
     */
    public function getRatesForSchedule(Schedule $schedule)
    {
        $caregiverRate = $schedule->caregiver_rate ?? $this->getDefaultCaregiverRate($schedule->caregiver, $schedule->fixed_rates);
        $providerFee = $schedule->provider_fee;
        $clientRate = $schedule->client_rate ?? $this->getDefaultClientRate($schedule->client, $schedule->fixed_rates);
        $paymentMethod = $schedule->client->defaultPayment ?? new CreditCard(); // use CC rates as default
        return $this->getRateObject(
            $paymentMethod,
            $schedule->business_id,
            $schedule->fixed_rates,
            $caregiverRate,
            $providerFee,
            $clientRate
        );
    }

    /**
     * @param \App\Client $client
     * @param \App\Caregiver $caregiver
     * @param bool $fixedRates
     * @param array|null $pivot
     * @return \App\Shifts\Rates
     */
    public function getRatesForClientCaregiver(Client $client, Caregiver $caregiver, $fixedRates = false, array $pivot = null)
    {
        if (!$pivot) {
            $pivot = (array) \DB::table('client_caregivers')->where('client_id', $client->id)
                ->where('caregiver_id', $caregiver->id)
                ->first();
        }

        $caregiverRate = $this->getCaregiverRateFromPivot($pivot, $fixedRates)
            ?? $this->getDefaultCaregiverRate($caregiver, $fixedRates);
        $providerFee = $this->getProviderFeeFromPivot($pivot, $fixedRates);
        $clientRate = $this->getClientRateFromPivot($pivot, $fixedRates)
            ?? $this->getDefaultClientRate($client, $fixedRates);
        $paymentMethod = $schedule->client->defaultPayment ?? new CreditCard(); // use CC rates as default

        return $this->getRateObject(
            $paymentMethod,
            $client->business_id,
            $fixedRates,
            $caregiverRate,
            $providerFee,
            $clientRate
        );
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

    protected function getCaregiverRateFromPivot($pivot, $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';
        return (float) array_get($pivot, "caregiver_${fieldId}_rate")
            ?: (float) $this->getRateFromCode(array_get($pivot, "caregiver_${fieldId}_id"))
            ?: null;
    }

    protected function getProviderFeeFromPivot($pivot, $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';
        return $pivot["provider_${fieldId}_fee"] ?? null;
    }

    protected function getClientRateFromPivot($pivot, $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';
        return (float) array_get($pivot, "client_${fieldId}_rate")
            ?: (float) $this->getRateFromCode(array_get($pivot, "client_${fieldId}_id"))
            ?: null;
    }

    protected function getRateFromCode(int $id = null)
    {
        // Todo: Optimize
        if (!$id) return null;
        return RateCode::where('id', $id)->value('rate');
    }
}
