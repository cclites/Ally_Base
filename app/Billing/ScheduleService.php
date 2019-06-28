<?php

namespace App\Billing;

use App\AuditableModel;
use App\Schedule;
use App\Data\ScheduledRates;

/**
 * \App\Billing\ScheduleService
 *
 * @property int $id
 * @property int $schedule_id
 * @property int $service_id
 * @property int|null $payer_id
 * @property string $hours_type
 * @property float $duration
 * @property float|null $client_rate
 * @property float|null $caregiver_rate
 * @property int $quickbooks_service_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Schedule $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ScheduleService extends AuditableModel
{
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Return a ScheduledRates object
     *
     * @return \App\Data\ScheduledRates
     */
    public function getRates(): ScheduledRates
    {
        return new ScheduledRates(
            $this->client_rate,
            $this->caregiver_rate,
            $this->schedule->fixed_rates,
            $this->hours_type
        );
    }

}