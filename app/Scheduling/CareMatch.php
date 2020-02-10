<?php

namespace App\Scheduling;

use App\Business;
use App\Caregiver;
use App\Client;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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
     * @var float
     */
    protected $minimumMatch = 0;

    /**
     * @var array  An array of english days,  ex: monday, tuesday
     */
    protected $preferences = [];

    /**
     * @var Carbon
     */
    protected $startsAt;

    /**
     * @var array
     */
    protected $daysOfWeek = [];

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

    function matchesActivities(array $activities, $minimumMatch=0)
    {
        $this->activities = $activities;
        $this->minimumMatch = $minimumMatch;
        return $this;
    }

    function matchesClientActivities(Client $client, $minimumMatch=0)
    {
        $planIds = $client->carePlans()->pluck('id');
        $activities = \DB::table('care_plan_activities')->whereIn('care_plan_id', $planIds)->groupBy('activity_id')->pluck('activity_id');
        return $this->matchesActivities($activities->toArray(), $minimumMatch);
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

    function matchesDaysOfTheWeek(array $days) {
        $this->daysOfWeek = $days;
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

    /**
     * Get CareMatch results from a provided query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Collection|Caregiver[]
     */
    public function getResults(Builder $query)
    {
        $this->queryActivities($query);
        $this->queryPreferences($query);
        $this->queryRating($query);
        $this->queryAvailabilityPreferences($query);
        $this->queryTime($query);
        $this->queryLocation($query);
        $this->queryOvertime($query);

        $results = $query->get();

        $results = $this->filterActivities($results);
        $results = $this->filterLocation($results);

        return $results->take($this->limit)->values();
    }

    /**
     * Get results relating to businesses an office user has access to
     *
     * @param \App\User $user
     * @return \App\Caregiver[]|\Illuminate\Database\Eloquent\Collection
     */
    function resultsForOfficeUser(User $user = null)
    {
        $query = Caregiver::forRequestedBusinesses(null, $user)->active();

        if ($this->forClient) {
            return $this->resultsForClient($this->forClient);
        }

        return $this->getResults($query);
    }

    /**
     * Get results matching only client assignments
     *
     * @param \App\Client $client
     * @return \App\Caregiver[]|\Illuminate\Database\Eloquent\Collection
     */
    function resultsForClient(Client $client)
    {
        $relation = $client->caregivers();
        $query = $relation->active()->getQuery();

        return $this->getResults($query);
    }

    /**
     * Get results that relate to a specific business
     *
     * @param \App\Business $business
     * @return \App\Caregiver[]|\Illuminate\Database\Eloquent\Collection
     */
    function resultsForBusiness(Business $business)
    {
        $query = Caregiver::forBusinesses([$business->id])->active();

        if ($this->forClient) {
            return $this->resultsForClient($this->forClient);
        }

        return $this->getResults($query);
    }

    protected function queryRating($builder)
    {
        return;
    }

    protected function queryAvailabilityPreferences($builder)
    {
        if (!$this->duration && !$this->startsAt) return;

        $builder->where(function($q) {
            $q->whereHas('availability', function ($q) {

                if ($this->startsAt) {

                    $q->where(function ($q) {
                        $end = $this->startsAt->copy()->addMinutes($this->duration);
                        $q->where($this->getTimeOfDay($this->startsAt->hour), 1)
                          ->orWhere($this->getTimeOfDay($end->hour), 1)
                          ->orWhere('available_start_time', '>=', $this->startsAt)
                          ->orWhere('available_end_time', '<=', $this->end);
                    });
                    // Add start date to daysOfWeek
                    $this->daysOfWeek = array_merge($this->daysOfWeek, [$this->startsAt->format('l')]);

                }
                foreach($this->daysOfWeek as $day) {
                    $q->where($day , 1);
                }
                if ($this->duration) {
                    $hours = $this->duration / 60;
                    $q->where('minimum_shift_hours', '<=', $hours)
                        ->where('maximum_shift_hours', '>=', $hours);
                }
            })->orDoesntHave('availability');
        });
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

    //Tie in here with Caregiver's time?
    protected function getTimeOfDay($hour)
    {
        if ($hour > 20 || $hour < 6) return 'night';
        if ($hour < 12) return 'morning';
        if ($hour < 17) return 'afternoon';
        return 'evening';
    }

    protected function queryLocation($builder)
    {
        return;
    }

    protected function queryActivities($builder)
    {
        // Just eager load for the filter method
        $builder->with('skills');
    }

    protected function queryPreferences($builder)
    {
        if ($certification = Arr::get($this->preferences, 'certification')) {
            $builder->where('certification', $certification);
        }

        if ($gender = Arr::get($this->preferences, 'gender')) {
            $this->queryCaregiverUser($builder, 'gender', $gender);
        }

        if ($smoking = Arr::get($this->preferences, 'smoking')) {
            $this->queryCaregiverUser($builder, 'smoking_okay', $smoking);
        }

        if ($pets_dogs = Arr::get($this->preferences, 'pets_dogs')) {
            $this->queryCaregiverUser($builder, 'pets_dogs_okay', $pets_dogs);
        }

        if ($pets_cats = Arr::get($this->preferences, 'pets_cats')) {
            $this->queryCaregiverUser($builder, 'pets_cats_okay', $pets_cats);
        }

        if ($pets_birds = Arr::get($this->preferences, 'pets_birds')) {
            $this->queryCaregiverUser($builder, 'pets_birds_okay', $pets_birds);
        }

//        if ($ethnicities = Arr::get($this->preferences, 'ethnicities')) {
//            $builder->whereIn('ethnicity', $ethnicities);
//        }
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

    protected function queryCaregiverUser($builder, $field, $operator, $value = null)
    {
        // This can be optimized later if we need to query the user table on multiple fields
        $builder->whereHas('user', function($q) use ($field, $operator, $value) {
            $q->where($field, $operator, $value);
        });
    }

    protected function filterActivities($results)
    {
        $total = count($this->activities);

        foreach($results as $caregiver) {
            // percentage
            $matching = [];
            if ($caregiver->skills->count()) {
                $matching = array_filter($this->activities, function($id) use ($caregiver) {
                    return $caregiver->skills->contains($id);
                });
            }
            $caregiver->activity_match = $total ? round(count($matching) / $total, 2) : 0;
            $caregiver->setRelation('skills', null); // unset relation
        }

        if ($this->minimumMatch > 0) {
            return $results->filter(function ($caregiver) {
                return $caregiver->activity_match >= $this->minimumMatch;
            });
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
