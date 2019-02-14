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
    public $clientRate;
    public $caregiverRate;
    public $fixedRates = false;
    public $hoursType;

    public function __construct(?float $clientRate, ?float $caregiverRate, bool $fixedRates = false, string $hoursType = "default")
    {
        $this->clientRate = $clientRate;
        $this->caregiverRate = $caregiverRate;
        $this->fixedRates = $fixedRates;
        $this->hoursType = $hoursType;
    }
}