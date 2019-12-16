<?php

namespace App\Shifts;

use App\Business;
use App\Businesses\SettingsRepository;
use App\Claims\ClaimableService;
use App\Shift;
use Carbon\Carbon;

class DurationCalculator
{
    const DEFAULT_METHOD = 'shift';

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    function getDuration(Shift $shift)
    {
        $method = $this->getRoundingMethod($shift);
        return (float) $this->$method($shift);
    }

    public function getDurationForClaimableService(ClaimableService $service)
    {
        $hours = $service->visit_start_time->diffInMinutes($service->visit_end_time) / 60;
        return (float) round(floor(round($hours * 4)) / 4, 2);
    }

    function getRoundingMethod(Shift $shift)
    {
        return $this->settings->get($shift->business_id, 'shift_rounding_method', self::DEFAULT_METHOD) . 'RoundingMethod';
    }

    function noneRoundingMethod(Shift $shift)
    {
        list($in, $out) = $this->getTimes($shift);
        return round($in->diffInMinutes($out) / 60, 2);
    }

    function shiftRoundingMethod(Shift $shift)
    {
        list($in, $out) = $this->getTimes($shift);
        $hours = $in->diffInMinutes($out) / 60;
        return round(floor(round($hours * 4)) / 4, 2);
    }

    function individualRoundingMethod(Shift $shift)
    {
        list($in, $out) = $this->getTimes($shift);
        $in->minute(floor(round($in->minute / 60 * 4)) * 15);
        $in->second(0);
        $out->minute(floor(round($out->minute / 60 * 4)) * 15);
        $out->second(0);
        return round($in->diffInMinutes($out) / 60, 2);
    }

    /**
     * @param \App\Shift $shift
     * @return Carbon[]
     */
    protected function getTimes(Shift $shift): array
    {
        $in = new Carbon($shift->checked_in_time);
        $out = new Carbon($shift->checked_out_time ?? 'now');
        return array($in, $out);
    }

}
