<?php
namespace App\Shifts;

class Rates
{
    /**
     * @var float
     */
    public $caregiver_rate;

    /**
     * @var float|null  Null meaning not yet calculated
     */
    public $provider_fee;

    /**
     * @var float
     */
    public $client_rate;

    /**
     * @var float|null  Null meaning not yet calculated
     */
    public $ally_fee;

    /**
     * @var float
     */
    public $total_rate;

    /**
     * @var boolean
     */
    public $client_rate_includes_fee;

    /**
     * @var bool
     */
    public $fixed_rates = false;


    /**
     * Rates constructor.
     * @param float $caregiver_rate
     * @param float $provider_fee
     * @param float $client_rate
     * @param float $ally_fee
     * @param bool $client_rate_includes_fee
     * @param bool $fixed_rates
     */
    public function __construct(
        float $caregiver_rate,
        ?float $provider_fee,
        float $client_rate,
        ?float $ally_fee,
        bool $client_rate_includes_fee = false,
        bool $fixed_rates = false
    )
    {
        $this->caregiver_rate = $caregiver_rate;
        $this->provider_fee = $provider_fee;
        $this->client_rate = $client_rate;
        $this->ally_fee = $ally_fee;
        $this->client_rate_includes_fee = $client_rate_includes_fee;
        $this->fixed_rates = $fixed_rates;

        if ($client_rate_includes_fee) {
            $this->total_rate = $this->client_rate;
        }
        else {
            $this->total_rate = (float) bcadd($this->client_rate, $this->ally_fee, 2);
        }
    }

}
