<?php

namespace App;

use App\Exceptions\MissingTimezoneException;
use App\Scheduling\RuleGenerator;
use App\Scheduling\RuleParser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * A far-future date, that likely won't ever be chosen directly, to represent a never-ending schedule
     */
    const FOREVER_ENDDATE = '2100-12-31';

    protected $table = 'schedules';
    protected $guarded = ['id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // For closed schedules before they start
        static::addGlobalScope('age', function ($builder) {
            $builder->whereColumn('start_date', '<=', 'end_date');
        });
    }

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
        return $this->belongsTo(CarePlan::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Mutate to use the rrule() method
     *
     * @return mixed
     */
    public function getRruleAttribute()
    {
        return $this->rrule();
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function isRecurring()
    {
        return !$this->isSingle();
    }

    public function isSingle()
    {
        return (strlen($this->rrule) === 0);
    }

    /**
     * Create a schedule exception
     *
     * @param $date
     *
     * @return \App\ScheduleException|false
     */
    public function createException($date)
    {
        $exception = new ScheduleException(['date' => $date]);
        if ($this->exceptions()->save($exception)) {
            return $exception;
        }
        return false;
    }

    /**
     * Close the schedule on all days on and after the specified $date
     *
     * @param $date
     */
    public function closeSchedule($date) {
        $last_date = (new \DateTime($date))
            ->sub(new \DateInterval('P1D'))
            ->format('Y-m-d');

        if ($last_date < $this->start_date) {
            if (!$this->shifts()->exists()) {
                return $this->delete();
            }
        }

        return $this->update(['end_date' => $last_date]);
    }

    /**
     * Output full RRULE, append UNTIL rule based on end time in database.
     * @return mixed
     */
    public function rrule()
    {
        if (!$this->attributes['rrule']) return null;
        return ($this->end_date == self::FOREVER_ENDDATE) ? $this->attributes['rrule']
            : $this->attributes['rrule'] . ';UNTIL=' . RuleGenerator::getUTCDate($this->getEndDateTime()->addHour()); // add an hour to handle DST shifts
    }

    /**
     * Produce a human readable string describing the schedule timing
     *
     * @param array $opts
     * @return string
     */
    public function humanReadable($opts = [])
    {
        $parser = new RuleParser($this->getStartDateTime(), $this->rrule());
        return $parser->humanReadable($opts);
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
        if (!$this->business->timezone) throw new MissingTimezoneException;
        return $this->business->timezone;
    }

    /**
     * Get the starting time of the first event
     *
     * @return \Carbon\Carbon
     */
    public function getStartDateTime()
    {
        return new Carbon($this->start_date . ' ' . $this->time, $this->getTimezone());
    }

    /**
     * Get the starting time of the last event
     *
     * @return \Carbon\Carbon
     */
    public function getEndDateTime()
    {
        return new Carbon($this->end_date . ' ' . $this->time, $this->getTimezone());
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
        if ((string) $this->caregiver_rate !== "") return $this->caregiver_rate;
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
        if ((string) $this->provider_fee !== "") return $this->provider_fee;
        if ($relation = $this->client->caregivers()->find($this->caregiver_id)) {
            if ($this->all_day) {
                return $relation->pivot->provider_daily_fee;
            }
            return $relation->pivot->provider_hourly_fee;
        }
        return 0;
    }
}
