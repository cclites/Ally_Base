<?php
namespace App\Billing\Validators;

use App\Billing\Payer;
use App\Billing\PayerRate;
use Illuminate\Support\Collection;

class PayerRateValidator
{
    /**
     * @var string|null
     */
    protected $error;

    public function validate(Payer $payer): bool
    {
        /** @var \App\Billing\PayerRate[] $rates */
        $rates = $payer->rates;

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
        /** @var \App\Billing\PayerRate[] $rates */
        $rates = $allRates->filter(function(PayerRate $rate) use ($date) {
            return $rate->effective_start <= $date && $rate->effective_end >= $date;
        });

        $list = [];
        foreach($rates as $rate) {
            if (isset($list[$rate->service_id])) {
                return $this->error("Two rates for the same service overlap on $date.");
            }
            $list[$rate->service_id] = 1;
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