<?php

namespace App\Scheduling;


use App\CreditCard;

class CostCalculator
{
    /**
     * Number of decimals to use in bcmath calculations
     */
    const DEFAULT_SCALE = 2;

    /**
     * @var \App\BankAccount|\App\CreditCard
     */
    protected $paymentType;

    /**
     * @var \App\Client
     */
    protected $client;

    /**
     * @var \App\Shift
     */
    protected $shift;

    /**
     * Supported client types
     * @var array
     */
    protected $clientTypes = ['private_pay', 'medicaid', 'LTCI', 'VA'];

    public function __construct($shift)
    {
        $this->shift = $shift;
        $this->client = $this->shift->client;
        if (!$this->client) throw new \Exception('Shift does not have a client, cannot calculate costs.');
    }

    public function setPaymentType($method)
    {
        $this->paymentType = $method;
        return $this;
    }

    public function getAllyFee()
    {
        if (!in_array($this->client->client_type, $this->clientTypes)) {
            throw new \Exception('Client type ' . $this->client->client_type . ' is not supported at this time.');
        }

        $pct = config('ally.bank_account_fee');
        switch($this->client->client_type) {
            case 'private_pay':
                if (!$this->paymentType) {
                    $this->paymentType = $this->client->defaultPayment;
                    if (!$this->paymentType) $this->paymentType = new CreditCard();
                }
                if ($this->paymentType instanceof CreditCard) {
                    $pct = config('ally.credit_card_fee');
                }
                // Default is bank account, so no more logic necessary
                break;
            default:
                // Medicaid fee is used for LTCI, VA, and Medicaid.  Expand the switch cases to add more.
                $pct = config('ally.medicaid_fee');
                break;
        }

        return bcmul(
            bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE),
            $pct,
            self::DEFAULT_SCALE
        );
    }

    public function getProviderFee()
    {
        if ($this->shift->all_day) {
            return round($this->shift->provider_fee, self::DEFAULT_SCALE);
        }
        return bcmul($this->shift->duration(), $this->shift->provider_fee, self::DEFAULT_SCALE);
    }

    public function getCaregiverCost()
    {
        if ($this->shift->all_day) {
            return round($this->shift->caregiver_rate, self::DEFAULT_SCALE);
        }
        return bcmul($this->shift->duration(), $this->shift->caregiver_rate, self::DEFAULT_SCALE);
    }

    public function getTotalCost()
    {
        return bcadd(
            bcadd($this->getProviderFee(), $this->getCaregiverCost(), self::DEFAULT_SCALE),
            $this->getAllyFee(),
            self::DEFAULT_SCALE
        );
    }
}