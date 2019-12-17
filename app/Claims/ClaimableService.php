<?php

namespace App\Claims;

use App\Activity;
use App\Business;
use App\Claims\Contracts\ClaimableInterface;
use App\Shifts\DurationCalculator;
use App\Signature;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ScrubsForSeeding;
use App\Billing\Service;
use App\AuditableModel;
use Carbon\Carbon;
use App\Shift;
use Packages\GMaps\GeocodeCoordinates;

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
     * Get the related Service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the related client signature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function clientSignature()
    {
        return $this->hasOne(Signature::class, 'id', 'client_signature_id');
    }

    /**
     * Get the related caregiver signature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caregiverSignature()
    {
        return $this->hasOne(Signature::class, 'id', 'caregiver_signature_id');
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

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
     * Get the display name of the Claimable Item.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->service_name . ' on ' . $this->getStartTime()->format('m/d/Y H:i A');
    }

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

    /**
     * Get the full text address.
     *
     * @return string
     */
    public function getAddress(): string
    {
        $address = $this->address1;
        if (filled($this->address2)) {
            $address .= " " . $this->address2;
        }
        $address .= " {$this->city}, {$this->state} {$this->zip}";
        return $address;
    }

    /**
     * Check if service has full EVV.
     *
     * @return bool
     */
    public function getHasEvv(): bool
    {
        return $this->has_evv == 1 ? true : false;
    }

    /**
     * Get collection of Activity objects from the comma
     * separated values stored on the service.
     *
     * @return iterable
     */
    public function getActivities() : iterable
    {
        if (empty($this->activities)) {
            return collect();
        }

        return Activity::whereIn('code', collect(explode(',', $this->activities)))
            ->get();
    }

    /**
     * Get the distance from the checked in location to the service address.
     *
     * @return float
     */
    public function getCheckedInDistance() : float
    {
        try {
            $checkInLocation = new GeocodeCoordinates($this->checked_in_latitude, $this->checked_in_longitude);
            $distance = $checkInLocation->distanceTo($this->latitude, $this->longitude);

            if (! $distance) {
                return (float) 0;
            }
            return (float) $distance;
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return (float) 0;
        }
    }

    /**
     * Get the distance from the checked out location to the service address.
     *
     * @return float
     */
    public function getCheckedOutDistance() : float
    {
        try {
            $checkOutLocation = new GeocodeCoordinates($this->checked_out_latitude, $this->checked_out_longitude);
            $distance = $checkOutLocation->distanceTo($this->latitude, $this->longitude);

            if (! $distance) {
                return (float) 0;
            }
            return (float) $distance;
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return (float) 0;
        }
    }

    /**
     * Return the number of hours for the service.  Requires
     * the related business in order to get the correct rounding method.
     *
     * @return float
     */
    public function getDuration() : float
    {
        return app(DurationCalculator::class)->getDurationForClaimableService($this);
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item): array
    {
        return [
            'address1' => $faker->streetAddress,
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
            'checked_in_number' => $faker->simple_phone,
            'checked_out_number' => $faker->simple_phone,
            'checked_in_latitude' => $faker->latitude,
            'checked_in_longitude' => $faker->longitude,
            'checked_out_latitude' => $faker->latitude,
            'checked_out_longitude' => $faker->longitude,
            'caregiver_comments' => $faker->sentence,
        ];
    }
}