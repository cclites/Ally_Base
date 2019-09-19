<?php

namespace App\Claims;

use App\Claims\Contracts\ClaimableInterface;
use App\Billing\Service;
use App\AuditableModel;
use App\Caregiver;
use App\Shift;
use Carbon\Carbon;

/**
 * App\Claims\ClaimableService
 *
 * @property int $id
 * @property int|null $shift_id
 * @property int|null $caregiver_id
 * @property string $caregiver_first_name
 * @property string $caregiver_last_name
 * @property string|null $caregiver_gender
 * @property string|null $caregiver_dob
 * @property null|string $caregiver_ssn
 * @property string|null $caregiver_medicaid_id
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \Illuminate\Support\Carbon $scheduled_start_time
 * @property \Illuminate\Support\Carbon $scheduled_end_time
 * @property \Illuminate\Support\Carbon $visit_start_time
 * @property \Illuminate\Support\Carbon $visit_end_time
 * @property \Illuminate\Support\Carbon|null $evv_start_time
 * @property \Illuminate\Support\Carbon|null $evv_end_time
 * @property string|null $checked_in_number
 * @property string|null $checked_out_number
 * @property float|null $checked_in_latitude
 * @property float|null $checked_in_longitude
 * @property float|null $checked_out_latitude
 * @property float|null $checked_out_longitude
 * @property int $has_evv
 * @property string|null $evv_method_in
 * @property string|null $evv_method_out
 * @property int|null $service_id
 * @property string $service_name
 * @property string|null $service_code
 * @property string|null $activities
 * @property string|null $caregiver_comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Billing\Service|null $service
 * @property-read \App\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableService query()
 * @mixin \Eloquent
 */
class ClaimableService extends AuditableModel implements ClaimableInterface
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'scheduled_start_time',
        'scheduled_end_time',
        'visit_start_time',
        'visit_end_time',
        'evv_start_time',
        'evv_end_time',
    ];

    const EVV_METHOD_TELEPHONY = 'telephony';
    const EVV_METHOD_GEOLOCATION = 'geolocation';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related Shift.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the related Caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    /**
     * Get the related Service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Encrypt Caregiver SSN on entry.
     *
     * @param $value
     */
    public function setCaregiverSsnAttribute($value)
    {
        $this->attributes['caregiver_ssn'] = $value ? \Crypt::encrypt($value) : null;
    }

    /**
     * Decrypt Caregiver SSN on retrieval.
     *
     * @return null|string
     */
    public function getCaregiverSsnAttribute()
    {
        return empty($this->attributes['caregiver_ssn']) ? null : \Crypt::decrypt($this->attributes['caregiver_ssn']);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    // **********************************************************
    // ClaimableInterface
    // **********************************************************

    /**
     * Get the name of the Claimable Item.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->service_name . ' ' . $this->service_code;
    }

    /**
     * Get the Caregiver's name that performed the service.
     *
     * @return string
     */
    public function getCaregiverName(): string
    {
        if (empty($this->caregiver_first_name) && empty($this->caregiver_last_name)) {
            return '';
        }

        return $this->caregiver_first_name . ' ' . $this->caregiver_last_name;
    }

    /**
     * Get the start time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getStartTime(): ?Carbon
    {
        return $this->visit_start_time;
    }

    /**
     * Get the end time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getEndTime(): ?Carbon
    {
        return $this->visit_end_time;
    }
}