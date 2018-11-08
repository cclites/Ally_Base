<?php

namespace App\Traits;

use App\RateCode;

/**
 * Trait HasDefaultRates
 * @package App\Traits
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property-read RateCode $defaultHourlyRate
 * @property-read RateCode $defaultFixedRate
 */
trait HasDefaultRates
{
    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function defaultHourlyRate()
    {
        return $this->belongsTo(RateCode::class, 'hourly_rate_id');
    }

    public function defaultFixedRate()
    {
        return $this->belongsTo(RateCode::class, 'fixed_rate_id');
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    /**
     * @param int|RateCode $rateCode
     */
    public function setDefaultHourlyRate($rateCode)
    {
        $id = is_numeric($rateCode) ? $rateCode : $rateCode->id;
        $this->update(['hourly_rate_id' => $id]);
    }

    /**
     * @param int|RateCode $rateCode
     */
    public function setDefaultFixedRate($rateCode)
    {
        $id = is_numeric($rateCode) ? $rateCode : $rateCode->id;
        $this->update(['fixed_rate_id' => $id]);
    }
}
