<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use When\When;

class Schedule extends Model
{
    protected $table = 'schedules';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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

        if ($this->getEndDateTime() < $end_date) {
            $end_date = $this->getEndDateTime();
        }

        $when = new When();
        $occurrences = $when->startDate($this->getStartDateTime())
            ->rrule($this->rrule)
            ->getOccurrencesBetween($start_date, $end_date, $limit);

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
}
