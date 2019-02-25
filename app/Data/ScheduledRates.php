<?php
namespace App\Data;

/**
 * Class ScheduledRates
 * Rate data used in both shifts and scheduling
 * 
 * @package App\Data
 */
class ScheduledRates
{
    private $clientRate;
    private $caregiverRate;
    private $fixedRates;
    private $hoursType;

    public function __construct(?float $clientRate, ?float $caregiverRate, bool $fixedRates = false, string $hoursType = "default")
    {
        $this->clientRate = $clientRate;
        $this->caregiverRate = $caregiverRate;
        $this->fixedRates = $fixedRates;
        $this->hoursType = $hoursType;
    }

    public function clientRate(): ?float
    {
        return $this->clientRate;
    }

    public function caregiverRate(): ?float
    {
        return $this->caregiverRate;
    }

    public function fixedRates(): bool
    {
        return $this->fixedRates;
    }

    public function hoursType(): string
    {
        return $this->hoursType;
    }
}