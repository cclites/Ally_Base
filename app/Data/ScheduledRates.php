<?php
namespace App\Data;

use App\Data\Traits\ReflectsToArray;
use JsonSerializable;

/**
 * Class ScheduledRates
 * Rate data used in both shifts and scheduling
 * 
 * @package App\Data
 */
class ScheduledRates implements JsonSerializable
{
    use ReflectsToArray;

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