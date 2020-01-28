<?php
namespace App\Billing\Validators;

use App\Client;
use App\Billing\ClientRate;
use Illuminate\Support\Collection;

class ClientRateValidator
{
    /**
     * @var string|null
     */
    protected $error;

    public function validate(Client $client): bool
    {
        /** @var \App\Billing\ClientRate[] $rates */
        $rates = $client->rates;

        // Build dates array based on the ranges
        $dates = $this->buildDatesArray($rates);

        // Validate against each date
        foreach($dates as $date) {
            if (!$this->validateByDate($rates, $date)) return false;
        }

        return true;
    }

    /**
     * @param \Illuminate\Support\Collection $allRates
     * @param string $date
     * @return bool
     */
    function validateByDate(Collection $allRates, string $date): bool
    {
        /** @var \App\Billing\ClientRate[] $rates */
        $rates = $allRates->filter(function(ClientRate $rate) use ($date) {
            return $rate->effective_start <= $date && $rate->effective_end >= $date;
        });

        $list = [];
        foreach($rates as $rate) {
            $hash = strval($rate->payer_id . '_' . $rate->service_id . '_' . $rate->caregiver_id);
            if (isset($list[$hash])) {
                return $this->error("Two rates for the same service and provider overlap on $date.");
            }
            $list[$hash] = 1;
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): string {
        return $this->error ?: '';
    }

    /**
     * @param string $message
     * @return false
     */
    protected function error(string $message): bool
    {
        $this->error = $message;
        return false;
    }

    /**
     * @param $rates
     * @return array
     */
    protected function buildDatesArray($rates): array
    {
        $dates = [];
        foreach ($rates as $rate) {
            $dates[] = $rate->effective_start;
            $dates[] = $rate->effective_end;
        }
        return $dates;
    }
}