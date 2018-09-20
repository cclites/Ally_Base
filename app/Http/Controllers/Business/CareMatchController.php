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
        $request->validate([
            'starts_at' => 'required|date',
            'duration' => 'integer',
            'matches_activities' => 'boolean',
            'matches_preferences' => 'boolean',
            'matches_existing_assignments' => 'boolean',
            'exclude_overtime' => 'boolean',
            'radius' => 'nullable|numeric',
            'rating' => 'nullable|numeric',
        ], [
            'starts_at.*' => 'The start date and time are required.',
            'duration.*' => 'The start time and end time are required.',
        ]);

        if ($request->starts_at) {
            $this->careMatch->matchesTime(Carbon::parse($request->starts_at, $this->business()->timezone), $request->duration);
        }

        if ($request->matches_activities) {
            $this->careMatch->matchesClientActivities($client);
        }

        if ($request->matches_preferences) {
            $this->careMatch->matchesClientPreferences($client);
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
