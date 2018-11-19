<?php

namespace App\Http\Controllers\Business;

use App\Client;
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

    function clientMatch(Request $request, Client $client)
    {
        $this->authorize('read', $client);

        $request->validate([
            'starts_at' => 'nullable|date',
            'duration' => 'nullable|integer|required_if:exclude_overtime,1',
            'matches_activities' => 'nullable|numeric', // should be a decimal representing the minimum percent match
//            'matches_preferences' => 'boolean',
            'matches_gender' => 'nullable|string',
            'matches_license' => 'nullable|string',
            'matches_language' => 'nullable|string',
            'matches_days' => 'nullable|array',
            'matches_existing_assignments' => 'boolean',
            'exclude_overtime' => 'boolean',
            'radius' => 'nullable|numeric',
            'rating' => 'nullable|numeric',
        ], [
            'starts_at.*' => 'The start date and time are invalid.',
            'duration.*' => 'The start time and end time are required for overtime calculations.',
        ]);

        $this->careMatch->matchesClientActivities($client, $request->matches_activities);

        if ($request->starts_at) {
            $this->careMatch->matchesTime(Carbon::parse($request->starts_at, $this->business()->timezone), $request->duration);
        }

//        if ($request->matches_preferences) {
//            $this->careMatch->matchesClientPreferences($client);
//        }

        if ($request->matches_gender) {
            $preferences['gender'] = $request->matches_gender === 'client' ? optional($client->preferences)->gender : $request->matches_gender;
        }

        if ($request->matches_license) {
            $preferences['license'] = $request->matches_license === 'client' ? optional($client->preferences)->license : $request->matches_license;
        }

        if ($request->matches_language) {
            $preferences['language'] = $request->matches_language === 'client' ? optional($client->preferences)->language : $request->matches_language;
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

        return $this->careMatch->get($this->business());
    }
}
