<?php
namespace App\Shifts;

use App\Billing\ClientRate;
use App\Billing\Contracts\ChargeableInterface;
use App\Businesses\SettingsRepository;
use App\Caregiver;
use App\Client;
use App\Contracts\HasAllyFeeInterface;
use App\Billing\Payments\Methods\CreditCard;
use App\RateCode;
use App\Schedule;
use App\Shift;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Data\ScheduledRates;
use App\Billing\Payer;

class RateFactory
{

    /**
     * @var \App\Businesses\SettingsRepository
     */
    protected $settings;

    /**
     * RateFactory constructor.
     * @param \App\Businesses\SettingsRepository $settings
     */
    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    ////////////////////////////////////
    //// NEW STRUCTURE (2019-01-13)
    ////////////////////////////////////

    /**
     * Apply overtime rate calculates to the given Rates object.
     *
     * @param Rates $rates
     * @param Client $client
     * @param Payer|null $payer
     * @return Rates
     */
    public function getOvertimeRates(Rates $rates, Client $client, ?Payer $payer = null) : Rates
    {
        return $this->multiplyRates(
            $rates,
            $this->settings->get($client->business_id, 'ot_behavior', null),
            floatval($this->settings->get($client->business_id, 'ot_multiplier', 1.5)),
            $client,
            optional($payer)->getPaymentMethod()
        );
    }

    /**
     * Apply holiday rate calculates to the given Rates object.
     *
     * @param Rates $rates
     * @param Client $client
     * @param Payer|null $payer
     * @return Rates
     */
    public function getHolidayRates(Rates $rates, Client $client, ?Payer $payer = null) : Rates
    {
        return $this->multiplyRates(
            $rates,
            $this->settings->get($client->business_id, 'hol_behavior', null),
            floatval($this->settings->get($client->business_id, 'hol_multiplier', 1.5)),
            $client,
            optional($payer)->getPaymentMethod()
        );
    }

    /**
     * Multiply rates based on action type and given multiplier and automatically  
     * recalculate the client_rate (total) including fees.
     *
     * @param Rates $rates
     * @param string|null $action
     * @param float $multiplier
     * @param Client $client
     * @param ChargeableInterface|null $paymentMethod
     * @return Rates
     */
    public function multiplyRates(Rates $rates, ?string $action, float $multiplier, Client $client, ?ChargeableInterface $paymentMethod = null) : Rates
    {
        $allyFee = AllyFeeCalculator::getFee($client, $paymentMethod, $rates->client_rate, true);
        $providerFee = $this->getProviderFee($rates->client_rate, $rates->caregiver_rate, $allyFee, true);

        switch ($action) {
            case 'caregiver':
                $rates->caregiver_rate = $rates->caregiver_rate * $multiplier;
                break;
            case 'provider':
                $providerFee = $providerFee * $multiplier; 
                break;
            case 'both':
                $rates->caregiver_rate = $rates->caregiver_rate * $multiplier;
                $providerFee = $providerFee * $multiplier; 
                break;
            default:
                return $rates;
        }

        $total = bcadd($providerFee, $rates->caregiver_rate, 2);
        $allyFee = AllyFeeCalculator::getFee($client, $paymentMethod, $total, false);
        $rates->client_rate = $this->getClientRate($providerFee, $rates->caregiver_rate, $allyFee, true);
        return $rates;
    }

    public function matchingRateExists(Client $client, string $effectiveDateYMD, ?int $serviceId = null, ?int $payerId = null, ?int $caregiverId = null): bool
    {
        $effectiveRates = $client->rates()
            ->where('effective_start', '<=', $effectiveDateYMD)
            ->where('effective_end', '>=', $effectiveDateYMD)
            ->get();

        $clientRate = $this->findMatchingClientRate($effectiveRates, $serviceId, $payerId, $caregiverId);

        return $clientRate !== null;
    }

    public function findMatchingRate(Client $client, string $effectiveDateYMD, bool $fixedRates = false, ?int $serviceId = null, ?int $payerId = null, ?int $caregiverId = null): Rates
    {
        $effectiveRates = $client->rates()
            ->where('effective_start', '<=', $effectiveDateYMD)
            ->where('effective_end', '>=', $effectiveDateYMD)
            ->get();

        $clientRate = $this->findMatchingClientRate($effectiveRates, $serviceId, $payerId, $caregiverId);

        if ($clientRate) {
            return new Rates(
                $fixedRates ? $clientRate->caregiver_fixed_rate : $clientRate->caregiver_hourly_rate,
                null,
                $fixedRates ? $clientRate->client_fixed_rate : $clientRate->client_hourly_rate,
                null,
                true,
                $fixedRates
            );
        }

        return new Rates(0, 0, 0, 0, false, $fixedRates);
    }

    protected function findMatchingClientRate(Collection $rates, ?int $serviceId = null, ?int $payerId = null, ?int $caregiverId = null): ?ClientRate
    {
        // First check for an exact match
        if ($rate = $this->searchRates($rates, $serviceId, $payerId, $caregiverId)) {
            return $rate;
        }

        // Find partial matches in order of caregiver ID, payer ID, then service ID
        if ($rate = $this->searchRates($rates, null, $payerId, $caregiverId)) {
            return $rate;
        }
        if ($rate = $this->searchRates($rates, $serviceId, null, $caregiverId)) {
            return $rate;
        }
        if ($rate = $this->searchRates($rates, null, null, $caregiverId)) {
            return $rate;
        }
        if ($rate = $this->searchRates($rates, $serviceId, $payerId, null)) {
            return $rate;
        }
        if ($rate = $this->searchRates($rates, null, $payerId, null)) {
            return $rate;
        }
        if ($rate = $this->searchRates($rates, $serviceId, null, null)) {
            return $rate;
        }

        // Find fallback/default rate or return null
        return $this->searchRates($rates, null, null, null);
    }

    protected function searchRates(Collection $rates, ?int $serviceId = null, ?int $payerId = null, ?int $caregiverId = null): ?ClientRate
    {
        return $rates->first(function(ClientRate $item) use ($serviceId, $payerId, $caregiverId) {
            return $item->service_id === $serviceId
                && $item->payer_id === $payerId
                && $item->caregiver_id === $caregiverId;
        });
    }


    public function hasNegativeProviderFee(HasAllyFeeInterface $entity, float $clientRate, float $caregiverRate): bool
    {
        $maxCaregiverRate = subtract($clientRate, $entity->getAllyFee($clientRate, true));
        return $caregiverRate > $maxCaregiverRate;
    }

    /**
     * Get the ally fee based on the payment method and effective charged rate
     *
     * @param \App\Contracts\HasAllyFeeInterface $paymentMethod
     * @param float $chargedRate
     * @param bool $allyFeeIncluded
     * @return float
     */
    function getAllyFee(HasAllyFeeInterface $paymentMethod, float $chargedRate, $allyFeeIncluded = true)
    {
        return (float) $paymentMethod->getAllyFee($chargedRate, $allyFeeIncluded);
    }

    ////////////////////////////////////
    //// OLD STRUCTURE
    ////////////////////////////////////

    /**
     * Get the rate structure setting of a given business
     *
     * @todo The new billing system as of February 2019 makes this obsolete.  All businesses use a client rate structure with the ally fee included due to split payer logic.
     * @param int $businessId
     * @return mixed
     */
    function getRateStructure(int $businessId)
    {
        return 'client_rate';
//        return $this->settings->get($businessId, 'rate_structure', 'provider_fee');
    }

    /**
     * Determine ally fee included setting of a given business
     *
     * @todo The new billing system as of February 2019 makes this obsolete.  All businesses use a client rate structure with the ally fee included due to split payer logic.
     * @param int $businessId
     * @return bool
     */
    function allyFeeIncluded(int $businessId)
    {
        return true;
        /*
        return $this->getRateStructure($businessId) === 'client_rate'
            && $this->settings->get($businessId, 'include_ally_fee', false);
        */
    }

    /**
     * Determine if the business is using rate codes (true) or free text rates (false)
     *
     * @param int $businessId
     * @return bool
     */
    function usingRateCodes(int $businessId)
    {
        return $this->getRateStructure($businessId) === 'client_rate'
            && $this->settings->get($businessId, 'use_rate_codes', false);
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
     * @param float $allyFee
     * @param bool $allyFeeIncluded
     * @return float
     */
    function getClientRate(float $providerFee, float $caregiverRate, float $allyFee, bool $allyFeeIncluded = false)
    {
        return (float) bcadd(
            bcadd($providerFee, $caregiverRate, 2),
            $allyFeeIncluded ? $allyFee : "0",
            2
        );
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
            if ($this->shouldRecalculateClientRate($clientRate, $caregiverRate, $allyFee)) {
                $clientRate = $this->getClientRate(0.0, $chargedRate, $allyFee, $allyFeeIncluded);
            }
            $providerFee = $this->getProviderFee($clientRate, $caregiverRate, $allyFee, $allyFeeIncluded);
        }
        else {
            $clientRate = $this->getClientRate($providerFee, $caregiverRate, $allyFee, false); // ally fee can't be included in this rate structure
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
     * Whether or not the clientRate needs to be re-calculated.  This method ensures the client rate is not less than the caregiver rate and ally fee.
     *
     * @param float $clientRate
     * @param float $caregiverRate
     * @param float $allyFee
     * @return bool
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
     */
    protected function shouldRecalculateClientRate(float $clientRate, float $caregiverRate, float $allyFee)
    {
        return ($caregiverRate + $allyFee) > $clientRate;
    }

    /**
     * @param \App\Shift $shift
     * @return \App\Shifts\Rates
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
     */
    public function getRatesForShift(Shift $shift)
    {
        // TODO IMPORTANT:  RATE PERSISTENCE FOR CHARGED SHIFTS
        // TODO: REPLACE BELOW COST HISTORY WITH NEW SHIFT COSTS LOGIC, RIGHT NOW THIS DOES NOT SUPPORT RATE CODES
        $allyPct = ($shift->costHistory) ? $shift->costHistory->ally_pct
            : $this->getPaymentMethod($shift->client)->getAllyPercentage();
        $chargedRate = bcadd($shift->caregiver_rate, $shift->provider_fee, 2);
        $allyFee = round(bcmul($chargedRate, $allyPct, 4), 2);

        return new Rates(
            $shift->caregiver_rate,
            $shift->provider_fee,
            $chargedRate,
            $allyFee,
            false,
            $shift->fixed_rates
        );
    }

    /**
     * @param \App\Schedule $schedule
     * @return \App\Shifts\Rates
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
     */
    public function getRatesForSchedule(Schedule $schedule)
    {
        // TODO: Optimize getRatesForClientCaregiver call
        $usingRateCodes = $this->usingRateCodes($schedule->business_id);
        $caregiverRate = $this->resolveRate($schedule->caregiver_rate, $schedule->caregiver_rate_id, $usingRateCodes)
            ?? $this->getRatesForClientCaregiver($schedule->client, $schedule->caregiver, $schedule->fixed_rates)->caregiver_rate;
        $providerFee = (float) $schedule->provider_fee;
        $clientRate = $this->resolveRate($schedule->client_rate, $schedule->client_rate_id, $usingRateCodes)
            ?? $this->getRatesForClientCaregiver($schedule->client, $schedule->caregiver, $schedule->fixed_rates)->client_rate;

        return $this->getRateObject(
            $this->getPaymentMethod($schedule->client),
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
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
     */
    public function getRatesForClientCaregiver(Client $client, Caregiver $caregiver, bool $fixedRates = false, array $pivot = null)
    {

        if (!$pivot) {
            $pivot = (array) \DB::table('client_caregivers')->where('client_id', $client->id)
                ->where('caregiver_id', $caregiver->id)
                ->first();
        }

        $usingRateCodes = $this->usingRateCodes($client->business_id);
        $caregiverRate = $this->getCaregiverRateFromPivot($pivot, $usingRateCodes, $fixedRates)
            ?? $this->getDefaultCaregiverRate($caregiver, $fixedRates);
        $providerFee = $this->getProviderFeeFromPivot($pivot, $usingRateCodes, $fixedRates) ?? 0.0;
        $clientRate = $this->getClientRateFromPivot($pivot, $usingRateCodes, $fixedRates)
            ?? $this->getDefaultClientRate($client, $fixedRates);

        return $this->getRateObject(
            $this->getPaymentMethod($client),
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
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
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
     * @deprecated @todo Needs to be updated before it can be relied on as of February 2019's rate updates
     */
    public function getDefaultCaregiverRate(Caregiver $caregiver, bool $fixedRates = false)
    {
        if ($fixedRates) {
            return $caregiver->defaultFixedRate ? $caregiver->defaultFixedRate->rate : 0.0;
        }

        return $caregiver->defaultHourlyRate ? $caregiver->defaultHourlyRate->rate : 0.0;
    }

    protected function getCaregiverRateFromPivot(array $pivot, bool $usingRateCodes, bool $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';

        return $this->resolveRate(
            Arr::get($pivot, "caregiver_${fieldId}_rate"),
            Arr::get($pivot, "caregiver_${fieldId}_id"),
            $usingRateCodes
        );
    }

    protected function getProviderFeeFromPivot(array $pivot, bool $usingRateCodes, bool $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';

        return $this->resolveRate(
            Arr::get($pivot, "provider_${fieldId}_fee"),
            Arr::get($pivot, "provider_${fieldId}_id"),
            $usingRateCodes
        );
    }

    protected function getClientRateFromPivot(array $pivot, bool $usingRateCodes, bool $fixedRates = false)
    {
        $fieldId = $fixedRates ? 'fixed' : 'hourly';

        return $this->resolveRate(
            Arr::get($pivot, "client_${fieldId}_rate"),
            Arr::get($pivot, "client_${fieldId}_id"),
            $usingRateCodes
        );
    }

    protected function getRateFromCode(int $id = null)
    {
        // Todo: Optimize, store rate code cache?
        if (!$id) return null;
        return RateCode::where('id', $id)->value('rate');
    }

    protected function resolveRate($freeTextValue, $rateCodeId, bool $usingRateCodes)
    {
        return $usingRateCodes ? $this->getRateFromCode((int) $rateCodeId) : $freeTextValue;
    }

    protected function getPaymentMethod(Client $client)
    {
        return $client->defaultPayment ?? new CreditCard();
    }
}
