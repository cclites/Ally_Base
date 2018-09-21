<?php

namespace App;

use App\Businesses\Timezone;
use App\Exceptions\MissingTimezoneException;
use App\Scheduling\RuleParser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


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
 * @property float $caregiver_rate
 * @property float $provider_fee
 * @property string $hours_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business $business
 * @property-read \App\CarePlan $carePlan
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScheduleException[] $exceptions
 * @property-read mixed $notes
 * @property-read \App\ScheduleNote|null $note
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereOvertimeDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereWeekday($value)
 * @mixin \Eloquent
 */
class Schedule extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'schedules';
    protected $guarded = ['id'];
    protected $dates = ['starts_at'];
    protected $with = ['business', 'note'];
    protected $appends = ['notes'];

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
    }

    ///////////////////////////////////////
    /// Schedule Statuses
    ///////////////////////////////////////

    const OK = 'OK';
    const ATTENTION_REQUIRED = 'ATTENTION_REQUIRED';
    const CAREGIVER_CANCELED = 'CAREGIVER_CANCELED';
    const CLIENT_CANCELED = 'CLIENT_CANCELED';

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

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getNotesAttribute()
    {
        return (string) $this->note;
    }

    public function getStartsAtAttribute()
    {
        return new Carbon($this->attributes['starts_at'], Timezone::getTimezone($this->business_id));
    }

    public function setStartsAtAttribute($value) {
        if ($value instanceof \DateTimeInterface && $this->business) {
            $value->setTimezone(new \DateTimeZone($this->business->timezone));
        }
        $this->attributes['starts_at'] = $value;
    }

    /**
     * Returns the first available connected shift that is currently
     * clocked in.
     *
     * @return bool
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

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

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
//        return $this->starts_at->isPast() ? self::MISSED_CLOCK_IN : self::SCHEDULED;
        return self::SCHEDULED;
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
        return $this->update(['note_id' => $note]);
    }

    /**
     * Remove the attached note from the schedule
     *
     * @return bool
     */
    public function deleteNote()
    {
        return $this->update(['note_id' => null]);
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

    /*
     * OLD
     */

    public function isRecurring()
    {
        return !$this->isSingle();
    }

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
        if (strlen($this->caregiver_rate)) return $this->caregiver_rate;
        if ($relation = $this->client->caregivers()->find($this->caregiver_id)) {
            if ($this->all_day) {
                return $relation->pivot->caregiver_daily_rate;
            }
            return $relation->pivot->caregiver_hourly_rate;
        }
        return 0;
    }

    /**
     * Calculate the provider scheduled fee
     *
     * @return int|mixed
     */
    public function getProviderFee()
    {
        if (strlen($this->provider_fee)) return $this->provider_fee;
        if ($relation = $this->client->caregivers()->find($this->caregiver_id)) {
            if ($this->all_day) {
                return $relation->pivot->provider_daily_fee;
            }
            return $relation->pivot->provider_hourly_fee;
        }
        return 0;
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
     * Get only schedules that are after right now.
     * Adjusts to timezone.
     *
     * @param [type] $query
     * @param [type] $timezone
     * @return void
     */
    public function scopeFuture($query, $timezone)
    {
        return $query->where('starts_at', '>=', Carbon::now($timezone)->subHour());
    }
}
