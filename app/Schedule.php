<?php

namespace App;

use App\Scheduling\RuleGenerator;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
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
     * @param int $limit
     *
     * @return \DateTime[]
     */
    public function getOccurrences($limit = 100)
    {
        return $this->getOccurrencesBetween($this->getStartDateTime(), $this->getEndDateTime(), 'UTC', $limit);
    }

    /**
     * @param $start_date
     * @param $end_date
     * @param string $timezone
     * @param int $limit
     *
     * @return \DateTime[]
     */
    public function getOccurrencesBetween($start_date, $end_date, $timezone='UTC', $limit = 100)
    {
        if (is_string($timezone)) $timezone = new \DateTimeZone($timezone);
        if (is_string($start_date)) $start_date = new \DateTime($start_date . ' 00:00:00', $timezone);
        if (is_string($end_date)) $end_date = new \DateTime($end_date . ' 23:59:59', $timezone);

        // Convert to UTC for consistency
        $start_date->setTimezone(new \DateTimeZone('UTC'));
        $end_date->setTimezone(new \DateTimeZone('UTC'));

        if ($start_date > $this->getEndDateTime()) {
            return [];
        }
        else if ($end_date < $this->getStartDateTime()) {
            return [];
        }
        else if ($this->isSingle()) {
            $occurrences = [];
            if ($this->getStartDateTime() >= $start_date && $this->getStartDateTime() <= $end_date) {
                $occurrences[] = new \DateTime($this->start_date . ' ' . $this->time, new \DateTimeZone('UTC'));
            }
        }
        else {
            if ($this->getEndDateTime() < $end_date) {
                $end_date = $this->getEndDateTime();
            }

            $when = new RuleGenerator();
            $occurrences = $when->startDate($this->getStartDateTime())
                                ->rrule($this->rrule)
                                ->getOccurrencesBetween($start_date, $end_date, $limit);
        }

        return $this->filterExceptions($occurrences);
    }

    public function getStartDateTime()
    {
        return new \DateTime($this->start_date . ' ' . $this->time, new \DateTimeZone('UTC'));
    }

    public function getEndDateTime()
    {
        $end = new \DateTime($this->end_date . ' ' . $this->time, new \DateTimeZone('UTC'));
        // Add one second to always include this as the last occurrence (needed for getOccurrencesBetween calculation)
        $end->add(new \DateInterval('PT1S'));
        return $end;
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
}
