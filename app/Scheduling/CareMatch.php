<?php

namespace App\Scheduling;

use App\Business;
use App\Caregiver;
use App\Client;
use Carbon\Carbon;

class CareMatch
{
    /**
     * @var Client
     */
    protected $forClient;

    /**
     * @var int[]
     */
    protected $activities = [];

    /**
     * @var array
     */
    protected $preferences = [];

    /**
     * @var Carbon
     */
    protected $startsAt;

    /**
     * @var float[]
     */
    protected $geocode;

    /**
     * @var float
     */
    protected $maximumMiles;

    /**
     * @var float
     */
    protected $minimumRating;

    /**
     * @var int
     */
    protected $duration;

    /**
     * @var bool
     */
    protected $excludeOvertime = false;

    /**
     * @var int
     */
    protected $limit = 500;

    function matchesExistingAssignments(Client $client)
    {
        $this->forClient = $client;
        return $this;
    }

    function matchesActivities(array $activities)
    {
        $this->activities = $activities;
        return $this;
    }

    function matchesClientActivities(Client $client)
    {
        $activities = [];
        // todo
        return $this->matchesActivities($activities);
    }

    function matchesPreferences(array $preferences)
    {
        $this->preferences = $preferences;
        return $this;
    }

    function matchesClientPreferences(Client $client)
    {
        $preferences = $client->preferences()->pluck('id')->toArray();
        return $this->matchesPreferences($preferences);
    }

    function matchesTime(Carbon $starts_at, int $duration) {
        $this->startsAt = $starts_at;
        $this->duration = $duration;
        return $this;
    }

    function matchesRadius(float $latitude, float $longitude, float $maximumMiles)
    {
        $this->geocode = [$latitude, $longitude];
        $this->maximumMiles = $maximumMiles;
        return $this;
    }

    function matchesClientRadius(Client $client, float $maximumMiles)
    {
        if ($address = $client->evvAddress) {
            if ($geocode = $address->getGeocode()) {
                $this->matchesRadius($geocode->latitude, $geocode->longitude, $maximumMiles);
            }
        }
        return $this;
    }

    function matchesRating(float $minimumRating)
    {
        $this->minimumRating = $minimumRating;
        return $this;
    }

    function excludeOvertime(int $duration)
    {
        $this->duration = $duration;
        $this->excludeOvertime = true;
        return $this;
    }

    function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    function get(Business $business)
    {
        $query = $business->caregivers();

        if ($this->forClient) {
            // Query only client caregivers
            $query = $this->forClient->caregivers();
        }

        $this->queryPreferences($query);
        $this->queryRating($query);
        $this->queryTime($query);
        $this->queryLocation($query);
        $this->queryOvertime($query);

        $results = $query->get();

        $results = $this->filterActivities($results);
        $results = $this->filterLocation($results);

        return $results->take($this->limit)->values();
    }

    protected function queryRating($builder)
    {
        return;
    }

    protected function queryTime($builder)
    {
        if (!$this->startsAt) return;
//        $builder->whereRaw('caregivers.id NOT IN (SELECT caregiver_id FROM schedules s9 WHERE s9.starts_at BETWEEN ? and ? OR s9.starts_at + INTERVAL s9.duration BETWEEN ? AND ?)')
        $builder->whereDoesntHave('schedules', function($q) {
            $end = $this->startsAt->copy()->addMinutes($this->duration);
            $q->whereRaw('(starts_at BETWEEN ? and ? OR starts_at + INTERVAL duration MINUTE BETWEEN ? AND ?)',
                [$this->startsAt, $end, $this->startsAt, $end]);
        });
    }

    protected function queryLocation($builder)
    {
        return;
    }

    protected function queryPreferences($builder)
    {
        if ($license = array_get($this->preferences, 'license')) {
            $builder->where('title', $license);
        }

        if ($gender = array_get($this->preferences, 'gender')) {
            $builder->where('gender', $gender);
        }
    }

    protected function queryOvertime($builder)
    {
        if (!$this->excludeOvertime) return;
        $builder->whereHas('schedules', function ($q) {
            $weekStart = $this->startsAt->copy()->startOfWeek();
            $weekEnd = $this->startsAt->copy()->endOfWeek();
            $overtimeDuration = 60 * 40 - $this->duration;
            $q->whereBetween('starts_at', [$weekStart, $weekEnd])
                ->whereRaw('SUM(duration) < ?', [$overtimeDuration]);
        });
    }

    protected function filterActivities($results)
    {
        // todo

        foreach($results as $result) {
            // percentage
            $result->activity_match = 100;
        }

        return $results;
    }

    protected function filterLocation($results)
    {
         foreach($results as $caregiver) {
            if (isset($this->geocode[1]) && $address = $caregiver->addresses->first()) {
                $caregiver->distance = $address->distanceTo($this->geocode[0], $this->geocode[1], 'mi');
            }
            if (!is_numeric($caregiver->distance)) {
                $caregiver->distance = 'Unavailable';
            }
        }

        if ($this->maximumMiles) {
            return $results->filter(function ($caregiver) {
                return is_numeric($caregiver->distance) && $caregiver->distance <= $this->maximumMiles;
            });
        }

        return $results;
    }
}
