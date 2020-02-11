<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Ethnicity;
use App\Http\Requests\ClientCareMatchRequest;
use App\Rules\ValidEnum;
use App\Scheduling\CareMatch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CareMatchController extends BaseController
{
    /**
     * @var \App\Scheduling\CareMatch
     */
    private $careMatch;

    function __construct(CareMatch $careMatch)
    {
        $this->careMatch = $careMatch;
    }

    function index()
    {
        return view('business.care_match.index');
    }

    /**
     * Match caregivers to the given client using the specified criteria.
     *
     * @param ClientCareMatchRequest $request
     * @param Client $client
     * @return \App\Caregiver[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    function clientMatch(ClientCareMatchRequest $request, Client $client)
    {
        $this->authorize('read', $client);

        $data = $request->validated();

        $this->careMatch->matchesClientActivities($client, $request->matches_activities);

        if ($request->starts_at) {
            $this->careMatch->matchesTime(Carbon::parse($request->starts_at, $client->business->getTimezone()), $request->duration);
        }

        if($request->shift_start){
            $this->careMatch->matchesShiftTime($request->shift_start, $request->shift_end);
        }

        if ($request->matches_gender) {
            $preferences['gender'] = $request->matches_gender === 'client' ? optional($client->preferences)->gender : $request->matches_gender;
        }

        if ($request->matches_certification) {
            $preferences['certification'] = $request->matches_certification === 'client' ? optional($client->preferences)->license : $request->matches_certification;
        }

        if ($request->matches_language) {
            $preferences['language'] = $request->matches_language === 'client' ? optional($client->preferences)->language : $request->matches_language;
        }

        if ($request->smoking) {
            if ($request->smoking == 'client') {
                if (optional($client->preferences)->smokes) {
                    // only add preference check if the client smokes, otherwise there
                    // is no need to narrow the search because allowed/no allowed would
                    // both fit clients that do not smoke.
                    $preferences['smoking'] = 1;
                }
            } else {
                $preferences['smoking'] = $request->smoking;
            }
        }
        
        if ($request->pets) {
            if ($request->pets == 'client') {
                if (optional($client->preferences)->pets_dogs) {
                    $preferences['pets_dogs'] = 1;
                }
                if (optional($client->preferences)->pets_cats) {
                    $preferences['pets_cats'] = 1;
                }
                if (optional($client->preferences)->pets_birds) {
                    $preferences['pets_birds'] = 1;
                }
            } else if ($request->pets == 'select') {
                if ($request->pets_dogs) {
                    $preferences['pets_dogs'] = $request->pets_dogs;
                }
                if ($request->pets_cats) {
                    $preferences['pets_cats'] = $request->pets_cats;
                }
                if ($request->pets_birds) {
                    $preferences['pets_birds'] = $request->pets_birds;
                }
            }
        }

        if ($request->ethnicity) {
            if ($request->ethnicity === 'client') {
                $preferences['ethnicities'] = optional($client->preferences)->getEthnicities();
            } else {
                $preferences['ethnicities'] = $request->ethnicities;
            }
        }

        if (isset($preferences)) {
            $this->careMatch->matchesPreferences(array_filter($preferences));
        }

        if ($request->matches_days) {
            $this->careMatch->matchesDaysOfTheWeek($request->matches_days);
        }

        if ($request->matches_existing_assignments) {
            $this->careMatch->matchesExistingAssignments($client);
        }

        if ($request->excludes_overtime) {
            $this->careMatch->excludeOvertime($request->duration);
        }

        $this->careMatch->matchesClientRadius($client, $request->radius ?: 500);

        if ($request->rating) {
            $this->careMatch->matchesRating($request->rating);
        }

        return $this->careMatch->resultsForOfficeUser();
    }
}
