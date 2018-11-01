<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\ClientOnboarding;
use App\Http\Controllers\Controller;
use App\OnboardingActivity;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientOnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function create(Client $client)
    {
        $onboardingActivities = OnboardingActivity::all();
        $activities = collect([
            'hands_on' => $onboardingActivities->where('category', 'hands_on')->values(),
            'household' => $onboardingActivities->where('category', 'household')->values()
        ]);

        $onboarding = null;
        $query = ClientOnboarding::with('activities')->where('client_id', $client->id);
        if ($query->exists()) {
            $onboarding = $query->first();
        }

        $clientData = collect($client->toArray())->only('id', 'firstname', 'lastname', 'email', 'date_of_birth', 'gender', 'onboarding_step');
        $clientData['date_of_birth'] = empty($clientData['date_of_birth']) ? $clientData['date_of_birth'] : Carbon::parse($clientData['date_of_birth'])->format('m/d/Y');
        return view('business.clients.onboarding', compact('client', 'activities', 'clientData', 'onboarding'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Client $client)
    {
        $clientData = collect($request->only('id', 'firstname', 'lastname', 'email', 'date_of_birth', 'gender'))
            ->filter()
            ->toArray();

        if (!empty($clientData['date_of_birth'])) {
            $clientData['date_of_birth'] = Carbon::parse($clientData['date_of_birth']);
        }
        $activities = collect($request->activities)->filter();
        $data = collect($request->except(
            'activities',
            'id',
            'firstname',
            'lastname',
            'email',
            'date_of_birth',
            'gender',
            'onboarding_step'
        ))
            ->filter()
            ->toArray();
        if (!empty($data['requested_start_at'])) {
            $data['requested_start_at'] = Carbon::parse($data['requested_start_at']);
        }

        $onboarding = DB::transaction(function () use ($client, $clientData, $activities, $data) {
            $clientData['onboarding_step'] = 2;
            $client->update($clientData);
            $onboarding = ClientOnboarding::updateOrCreate(
                ['client_id' => $client->id],
                $data
            );
            foreach ($activities as $key => $value) {
                if (!$onboarding->activities()->where('onboarding_activity_id', $key)->exists()) {
                    $onboarding->activities()->attach($key, ['assistance_level' => $value]);
                } else {
                    $onboarding->activities()->updateExistingPivot($key, ['assistance_level' => $value]);
                }
            }
            $onboarding->load('activities');
            return $onboarding;
        });

        if (!$onboarding) {
            return new ErrorResponse(500, 'Error saving.');
        }
        return new SuccessResponse('Client data saved.', compact('onboarding'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientOnboarding  $clientOnboarding
     * @return \Illuminate\Http\Response
     */
    public function show(ClientOnboarding $clientOnboarding)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientOnboarding  $clientOnboarding
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientOnboarding $clientOnboarding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientOnboarding  $clientOnboarding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientOnboarding $clientOnboarding)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientOnboarding  $clientOnboarding
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientOnboarding $clientOnboarding)
    {
        //
    }
}
