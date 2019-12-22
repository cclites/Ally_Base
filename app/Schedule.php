<?php
namespace App;

use App\Billing\ScheduleService;
use App\Billing\Service;
use App\Businesses\Timezone;
use App\Contracts\BelongsToBusinessesInterface;
use App\Exceptions\MissingTimezoneException;
use App\Data\ScheduledRates;
use App\Scheduling\RuleParser;
use App\Shifts\RateFactory;
use App\Shifts\ScheduleConverter;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;


/**
 * App\Schedule
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_id
 * @property int|null $caregiver_id
 * @property int $weekday
 * @property \Carbon\Carbon $starts_at
 * @property int $duration
 * @property int $overtime_duration
 * @property int|null $note_id
 * @property int $fixed_rates
 * @property int|null $caregiver_rate_id
 * @property int|null $client_rate_id
 * @property int|null $provider_fee_id
 * @property float|null $caregiver_rate
 * @property float|null $provider_fee
 * @property float|null $client_rate
 * @property string $hours_type
 * @property int|null $care_plan_id
 * @property string $status
 * @property string|null $converted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property bool $added_to_past
 * @property int $quickbooks_service_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\CarePlan|null $carePlan
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScheduleException[] $exceptions
 * @property-read \Illuminate\Database\Eloquent\Collection|ScheduleService[] $services
 * @property-read bool $clocked_in_shift
 * @property-read mixed $notes
 * @property-read mixed $shift_status
 * @property-read \App\ScheduleNote|null $note
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\Audit $auditTrail
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule forClient($client)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule future($timezone)
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCarePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereConvertedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereFixedRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereOvertimeDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereProviderFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereWeekday($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule withoutTrashed()
 * @mixin \Eloquent
 */
class Schedule extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;
    use SoftDeletes;

    protected $table = 'schedules';
    protected $guarded = ['id'];
    protected $dates = ['starts_at'];
    protected $with = ['business', 'note'];
    protected $appends = ['notes'];
    protected $orderedColumn = 'starts_at';
    protected $casts = [
        'fixed_rates' => 'boolean',
        'duration' => 'integer',
        'caregiver_rate' => 'float',
        'client_rate' => 'float',
        'provider_fee' => 'float',
        'added_to_past' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Exclude schedules for deleted clients
        static::addGlobalScope('hasClient', function ($builder) {
            $builder->has('client');
        });

        static::updating( function( $schedule ){

            $original = $schedule->getOriginal();
            $dirty = $schedule->getDirty();

            if( !empty( $original[ 'caregiver_id' ] ) && array_key_exists( 'caregiver_id', $dirty ) && $dirty[ 'caregiver_id' ] !== $original[ 'caregiver_id' ] ){
                // this covers a change from cg->cg as well as a change from cg->null

                // this is apparently more efficient than $model->relationship->contains in eloquent
                DB::table( 'caregiver_schedule_requests' )
                    ->where( 'caregiver_id', $original[ 'caregiver_id' ] )
                    ->where( 'schedule_id', $schedule->id )
                    ->update([ 'status' => CaregiverScheduleRequest::REQUEST_DENIED ]);

                // if theres a new cg, update their request if exists
                if( !empty( $dirty[ 'caregiver_id' ] ) ){

                    DB::table( 'caregiver_schedule_requests' )
                        ->where( 'caregiver_id', $dirty[ 'caregiver_id' ] )
                        ->where( 'schedule_id', $schedule->id )
                        ->update([ 'status' => CaregiverScheduleRequest::REQUEST_APPROVED ]);
                }
            }
        });
    }

    ///////////////////////////////////////
    /// Schedule Statuses
    ///////////////////////////////////////

    const OK = 'OK';
    const ATTENTION_REQUIRED = 'ATTENTION_REQUIRED';
    const CAREGIVER_CANCELED = 'CAREGIVER_CANCELED';
    const CLIENT_CANCELED = 'CLIENT_CANCELED';
    const CAREGIVER_NOSHOW = 'CAREGIVER_NOSHOW';
    const OPEN_SHIFT = 'OPEN_SHIFT';
    const HOSPITAL_HOLD = 'HOSPITAL_HOLD';

    ///////////////////////////////////////////
    /// Related Shift Statuses
    ///////////////////////////////////////////

    const SCHEDULED = 'SCHEDULED';
    const MISSED_CLOCK_IN = 'MISSED_CLOCK_IN';
    const CLOCKED_IN = 'CLOCKED_IN';
    const CONFIRMED = 'CONFIRMED';
    const UNCONFIRMED = 'UNCONFIRMED';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'schedule_activities');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function exceptions()
    {
        return $this->hasMany(ScheduleException::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function carePlan()
    {
        return $this->belongsTo(CarePlan::class)
            ->with('activities');
    }

    public function note()
    {
        return $this->belongsTo(ScheduleNote::class, 'note_id');
    }

    public function services()
    {
        return $this->hasMany(ScheduleService::class);
    }

    public function service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

    public function group()
    {
        return $this->belongsTo(ScheduleGroup::class, 'group_id');
    }

    public function schedule_requests()
    {
        return $this->hasMany( CaregiverScheduleRequest::class );
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////


    public function setStartsAtAttribute($value) {
        if ($value instanceof \DateTimeInterface && $this->business) {
            $value->setTimezone(new \DateTimeZone($this->business->timezone));
        }
        $this->attributes['starts_at'] = $value;
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    /**
     * gets the latest shift request for a given caregiver, or null if there is none
     */
    public function latest_request_for( $caregiver_id )
    {
        return optional( $this->schedule_requests()->where( 'caregiver_id', $caregiver_id )->first() )->pivot;
    }

    /**
     * is this the only criteria?
     */
    public function getIsOpenAttribute()
    {
        return $this->caregiver_id === null;
    }

    /**
     * Get whether of not the schedule will be converted by
     * the schedule converter CRON.
     *
     * @return bool
     * @throws MissingTimezoneException
     */
    public function getWillBeConvertedAttribute()
    {
        // Note: Logic here is reflected in the ScheduleConverter class.
        if (! in_array($this->status, ScheduleConverter::$convertibleStatuses)) {
            return false;
        }

        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $start = Carbon::now($this->getTimezone())->startOfWeek();
        if (Carbon::now()->dayOfWeek === Carbon::MONDAY) {
            // If monday morning, still use last week
            $start->subWeek();
        }

        return $this->starts_at->greaterThanOrEqualTo($start);
    }

    public function getNotesAttribute()
    {
        return (string) $this->note;
    }

    public function getStartsAtAttribute()
    {
        return Carbon::parse($this->attributes['starts_at'], $this->getTimezone());
    }

    /**
     * Returns the first available connected shift that is currently
     * clocked in.
     *
     * @return \App\Shift|null
     */
    public function getClockedInShiftAttribute()
    {
        foreach($this->shifts as $shift)
        {
            if ($shift->statusManager()->isClockedIn()) return $shift;
        }
        return null;
    }

    public function getShiftStatusAttribute()
    {
        return $this->getShiftStatus();
    }

    /**
     * @param iterable $services
     */
    public function syncServices(iterable $services)
    {
        $savedIds = [];
        foreach($services as $data) {
            $service = null;
            if (isset($data['id'])) {
                $service = $this->services()->find($data['id']);
            }
            if (!$service) {
                $service = new ScheduleService();
            }
            $service->fill($data);
            $this->services()->save($service);
            $savedIds[] = $service->id;
        }
        // Delete Others
        $this->services()->whereNotIn('id', $savedIds)->delete();
    }

    public function getGroupStatistics()
    {
        return optional($this->group)->getStatistics($this->getTimezone(), $this->starts_at->toDateString());
    }

    /**
     * Return the related shift status
     *
     * @return string
     */
    public function getShiftStatus()
    {
        if ($this->shifts->count()) {
            if ($this->isClockedIn()) {
                return self::CLOCKED_IN;
            }

            if ($this->isConfirmed()) {
                return self::CONFIRMED;
            }

            return self::UNCONFIRMED;
        }
        // Suppress missed clock in status for now
        // return $this->starts_at->isPast() ? self::MISSED_CLOCK_IN : self::SCHEDULED;
        return self::SCHEDULED;
    }

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
            $this->fixed_rates,
            $this->hours_type
        );
    }

    /**
     * Attach a schedule note to the schedule
     *
     * @param int|\App\ScheduleNote $note
     * @return bool
     */
    public function attachNote($note)
    {
        if (!is_numeric($note)) {
            if (empty($note->id)) return false;
            $note = $note->id;
        }
        $result = $this->note()->associate($note)->save();
        $this->load('note');
        return $result;
    }

    /**
     * Remove the attached note from the schedule
     *
     * @return bool
     */
    public function deleteNote()
    {
        return $this->note()->dissociate()->save();
    }

    /**
     * See if an active shift is clocked in on this schedule
     * Dev Note:  Eager loading shifts is ideal if using this on a collection
     *
     * @return bool
     */
    public function isClockedIn()
    {
        foreach($this->shifts as $shift)
        {
            if ($shift->statusManager()->isClockedIn()) return true;
        }
        return false;
    }

    /*
     * @return bool
     */
    public function isConfirmed()
    {
        foreach($this->shifts as $shift)
        {
            if ($shift->statusManager()->isConfirmed()) return true;
        }
        return false;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isRecurring()
    {
        return !$this->isSingle();
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isSingle()
    {
        return (strlen($this->rrule) === 0);
    }


    /**
     * @param int $limit
     *
     * @return \DateTime[]
     */
    public function getOccurrences($limit = 100)
    {
        return $this->getOccurrencesBetween($this->getStartDateTime(), $this->getEndDateTime(), $limit);
    }

    /**
     * Get schedule occurrences that overlap at any time between $start_date and $end_date (takes duration into account)
     *
     * @param $start_date
     * @param $end_date
     * @param string $timezone
     * @param int $limit
     *
     * @return \DateTime[]
     */
    public function getOccurrencesBetween($start_date, $end_date, $limit = 100)
    {
        if (is_string($start_date)) $start_date = new Carbon($start_date . ' 00:00:00', $this->getTimezone());
        if (is_string($end_date)) $end_date = new Carbon($end_date . ' 23:59:59', $this->getTimezone());

        // Force creation of a new Carbon instance to avoid mutations
        $start_date = Carbon::instance($start_date);

        // Subtract the duration of the event to allow for events that may have already started but not finished
        $start_date->subMinute($this->duration);

        return $this->getOccurrencesStartingBetween($start_date, $end_date, $limit);
    }

    /**
     * Get schedule occurrences that start between $start_date and $end_date (does not take duration into account)
     *
     * @param $start_date
     * @param $end_date
     * @param string $timezone
     * @param int $limit
     *
     * @return \DateTime[]
     */
    public function getOccurrencesStartingBetween($start_date, $end_date, $limit = 100)
    {
        if (is_string($start_date)) $start_date = new Carbon($start_date . ' 00:00:00', $this->getTimezone());
        if (is_string($end_date)) $end_date = new Carbon($end_date . ' 23:59:59', $this->getTimezone());

        // Force creation of a new Carbon instance to avoid mutations
        $start_date = Carbon::instance($start_date);

        if ($start_date > $this->getEndDateTime()) {
            return [];
        }
        else if ($end_date < $this->getStartDateTime()) {
            return [];
        }
        else if ($this->isSingle()) {
            $occurrences = [];
            if ($this->getStartDateTime() >= $start_date && $this->getStartDateTime() <= $end_date) {
                $occurrences[] = new Carbon($this->start_date . ' ' . $this->time, $this->getTimezone());
            }
        }
        else {
            $parser = new RuleParser($this->getStartDateTime(), $this->rrule());
            $occurrences = $parser->getOccurrencesBetween($start_date, $end_date, $limit);
        }

        return $this->filterExceptions($occurrences);
    }

    public function getTimezone()
    {
        if (!Timezone::getTimezone($this->business_id)) throw new MissingTimezoneException;
        return Timezone::getTimezone($this->business_id);
    }

    /**
     * Get the starting time of the first event
     *
     * @return \Carbon\Carbon
     */
    public function getStartDateTime()
    {
        return $this->starts_at;
    }

    /**
     * Get the starting time of the last event
     *
     * @return \Carbon\Carbon
     */
    public function getEndDateTime()
    {
        return $this->starts_at->copy()->addMinutes($this->duration);
    }

    /**
     * @param \DateTime[] $dates
     *
     * @return \DateTime[]
     */
    protected function filterExceptions(array $dates)
    {
        $exceptionsArray = $this->exceptions->pluck('date')->all();
        return array_filter($dates, function($date) use ($exceptionsArray) {
            return !in_array($date->format('Y-m-d'), $exceptionsArray);
        });
    }

    /**
     * Define the model parameters for a single event
     *
     * @param $date
     * @param $time
     * @param $duration
     *
     * @return $this
     */
    public function setSingleEvent($date, $time, $duration)
    {
        $this->start_date = $date;
        $this->end_date = (new \DateTime($date . ' ' . $time))
            ->add(new \DateInterval('PT' . $duration . 'M'))
            ->format('Y-m-d');
        $this->time = $time;
        $this->duration = $duration;
        $this->rrule = null;
        return $this;
    }

    /**
     * Calculate the caregiver scheduled rate
     *
     * @return int|mixed
     */
    public function getCaregiverRate()
    {
        return app(RateFactory::class)->getRatesForSchedule($this)->caregiver_rate
            ?? $this->caregiver_rate
            ?? 0;
    }

    /**
     * Calculate the provider scheduled fee
     *
     * @return int|mixed
     */
    public function getProviderFee()
    {
        return app(RateFactory::class)->getRatesForSchedule($this)->provider_fee
            ?? $this->provider_fee
            ?? 0;
    }

    /**
     * Determine if the schedule can still be clocked in to
     *
     * @return bool
     */
    public function canBeClockedIn()
    {
        return $this->shifts()->count() === 0;
    }

    /**
     * Check if the schedule has any services marked as 
     * overtime or holiday pay.
     *
     * @return boolean
     */
    public function hasOvertime()
    {
        if ($this->services->count()) {
            foreach ($this->services as $service) {
                if (in_array($service->hours_type, ['overtime', 'holiday'])) {
                    return true;
                }
            }
        } else {
            return in_array($this->hours_type, ['overtime', 'holiday']);
        }

        return false;
    }

    /**
     * Get the estimated billable hours of the schedule based on
     * service and/or payer including service breakouts.
     *
     * @param int|null $service_id
     * @param int|null $payer_id
     * @return float
     */
    public function getBillableHours(?int $service_id = null, ?int $payer_id = null) : float
    {
        if ($this->fixed_rates || ! empty($this->service_id)) {
            // actual hours shift
            if (! empty($service_id) && $service_id != $this->service_id) {
                // make sure service id matches the one on the model (or all).
                return 0;
            }
            if (! empty($payer_id) && $this->payer_id != $payer_id) {
                // make sure payer id matches the one on the model.
                return 0;
            }

            return empty($this->duration) ? floatval(0) : (floatval($this->duration) / floatval(60));
        } else if ($this->services->isNotEmpty()) {
            // service breakout shift
            $services = $this->services;

            if (! empty($service_id)) {
                $services = $services->where('service_id', $service_id);
            }

            if (! empty($payer_id)) {
                $services = $services->where('payer_id', $payer_id);
            }

            return $services->reduce(function ($sum, $service) {
                return add($sum, floatval($service->duration));
            }, 0);
        } else {
            return floatval(0);
        }
    }

    public function getBillableHoursForDay(Carbon $date, ?int $service_id = null, ?int $payer_id = null) : float
    {
        $hours = $this->getBillableHours($service_id, $payer_id);
        if (count($this->getDateSpan()) === 1) { // only spans 1 day
            return $hours;
        }

        if ($this->services->isNotEmpty()) {
            // service breakout shift - return total shift hours for all days since this
            // is a complicated query and we want to protect them more than be 100% accurate
            return $hours;
        } else {
            // actual hours schedule
            // TODO: this does not properly handle schedules that expand more than two days
            $start = $this->starts_at->copy();
            $end = $start->copy()->addMinutes($this->duration);

            if ($start->format('Ymd') === $date->format('Ymd')) {
                $minutes = $start->diffInMinutes($start->copy()->endOfDay());
                return $minutes === 0 ? 0 : ($minutes / 60);
            } else {
                $minutes = $end->copy()->startOfDay()->diffInMinutes($end);
                return $minutes === 0 ? 0 : ($minutes / 60);
            }
        }
    }

    /**
     * Get all of the dates that the shift exists on.
     *
     * @return array
     */
    public function getDateSpan() : array
    {
        // Convert shift dates to the client timezone so they are relative to ClientAuthorizations.
        $start = $this->starts_at->copy()->setTime(0, 0, 0);
        $end = $this->starts_at->copy()->addMinutes($this->duration)->setTime(0, 0, 0);

        if ($start->format('Ymd') == $end->format('Ymd')) {  // same day
            return [$start];
        }

        // TODO: this does not properly handle shifts that expand more than two days
        return [$start, $end];
    }

    /**
     * Get a collection of all the schedule services, whether
     * it is a breakout or regular schedule.
     *
     * @return Collection
     */
    public function getServices() : Collection
    {
        if ($this->services->count() > 0) {
            return $this->services->map(function (ScheduleService $scheduleService) {
                return $scheduleService->service;
            })->toBase();
        }

        return collect([$this->service]);
    }

    ///////////////////////////////////////////
    /// Static Methods
    ///////////////////////////////////////////

    /**
     * Get the caregiver information for the schedules surrounding
     * the given start and end times for the specified client.
     *
     * @param \App\Client $client
     * @param \Carbon\Carbon $startTime
     * @param \Carbon\Carbon $endTime
     * @param int $ignoreCaregiverId
     * @param int|null $windowSize
     * @return array
     */
    public static function getAdjoiningCaregiverSchedules(Client $client, $startTime, $endTime, int $ignoreCaregiverId, ?int $windowSize = 4) : array
    {
        $beforeWindow = [
            $startTime->copy()->subHours($windowSize),
            $startTime->subMinute()
        ];

        $afterWindow = [
            $endTime->copy()->addMinute(),
            $endTime->copy()->addHours($windowSize)
        ];

        return [
            $client->schedules()
                ->with('caregiver.phoneNumber')
                ->where('caregiver_id', '<>', $ignoreCaregiverId)
                ->whereHas('caregiver')
                ->whereBetween('starts_at', $beforeWindow)
                ->get()
                ->unique('caregiver_id'),
            $client->schedules()
                ->with('caregiver.phoneNumber')
                ->where('caregiver_id', '<>', $ignoreCaregiverId)
                ->whereHas('caregiver')
                ->whereBetween('starts_at', $afterWindow)
                ->get()
                ->unique('caregiver_id')
        ];
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Get only schedules that can be clocked in.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeThatCanBeClockedIn($query)
    {
        return $query->whereDoesntHave('shifts');
    }

    /**
     * Get only schedules for the given client.
     *
     * @param [type] $query
     * @param Client|Array|int $client_id
     * @return void
     */
    public function scopeForClient($query, $client)
    {
        if (is_object($client)) {
            $client = $client->id;
        } elseif (is_array($client)) {
            $client = $client['id'];
        }

        return $query->where('client_id', $client);
    }

    /**
     * Filter schedules by caregiver.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $caregiver
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForCaregiver($query, $caregiver)
    {
        if (empty($caregiver)) {
            return $query;
        }

        if (is_object($caregiver)) {
            $caregiver = $caregiver->id;
        } elseif (is_array($caregiver)) {
            $caregiver = $caregiver['id'];
        }

        return $query->where('caregiver_id', $caregiver);
    }

    /**
     * Get only schedules that are after right now.
     * Adjusts to timezone.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $timezone
     * @param string $fromDate
     * @return void
     */
    public function scopeFuture($query, $timezone, $fromDate = 'now')
    {
        $from = Carbon::parse($fromDate, $timezone)->subHour();

        $query->where('starts_at', '>=', $from);
    }

    /**
     * Get only schedules that are open, without a caregiver
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWhereOpen($query)
    {
        $query->whereDoesntHave( 'caregiver' );
    }

    /**
     * Get only schedules that start between now and 31 days from now
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $timezone
     * @param string $start
     * @param string $end
     * @return void
     */
    public function scopeInTheNextMonth($query, $timezone )
    {
        $query->whereBetween( 'starts_at', [

            Carbon::parse( 'now', $timezone ),
            Carbon::parse( 'today +31 days', $timezone )
        ]);
    }

    /**
     * Only include shifts that can still be clocked in to.
     *
     * @see self::canBeClockedIn()   This should be the same logic as canBeClockedIn()
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function scopeCanBeClockedIn(Builder $builder)
    {
        // Only allow service breakout and fixed rate shifts to be clocked in to once
        $builder->where(function($q) {
            $q->where('fixed_rate', false)
                ->orWhereDoesntHave('services')
                ->orWhereDoesntHave('shifts');
        });
    }

    /**
     * Get only schedules that start between the two given dates.
     * Adjusts to timezone.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $timezone
     * @param string $start
     * @param string $end
     * @return void
     */
    public function scopeStartsBetweenDates($query, $timezone, $start, $end)
    {
        $query->whereBetween('starts_at', [
            Carbon::parse($start, $timezone),
            Carbon::parse($end, $timezone)
        ]);
    }

    /**
     * Get schedules that exist between the given times.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param Carbon $start
     * @param Carbon $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeBetweenDates($query, Carbon $start, Carbon $end)
    {
        switch(\DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                $endFormat = "`starts_at` + INTERVAL `duration` MINUTE";
                break;
            case 'sqlite':
                $endFormat = "datetime(starts_at, '+' || duration || ' minutes')";
                break;
        }

        return $query->whereRaw(
                '( (starts_at >= ? AND starts_at <= ?) OR (starts_at < ? AND ' . $endFormat . ' >= ?) )',
                [$start, $end, $start, $start]
            );
    }



    /**
     * Gets a formatted list of audits.
     *
     * @return array
     */
    public function auditTrail()
    {
        return Audit::where('auditable_id' , $this->id)
                ->orWhere('url' , 'like', '%' . 'business/schedule/' . $this->id . '%')
                ->get();
    }
}
