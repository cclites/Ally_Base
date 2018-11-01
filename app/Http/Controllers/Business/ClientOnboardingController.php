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

        $clientData = collect($client->toArray())->only('id', 'firstname', 'lastname', 'email', 'date_of_birth', 'gender');
        return view('business.clients.onboarding', compact('client', 'activities', 'clientData'));
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
        if (isset($clientData['date_of_birth'])) {
            $clientData['date_of_birth'] = Carbon::parse($clientData['date_of_birth']);
        }
        $activities = collect($request->activities)->filter();
        $data = collect($request->except('activities', 'id', 'firstname', 'lastname', 'email', 'date_of_birth', 'gender'))
            ->filter()
            ->toArray();
        if (isset($data['requested_start_at'])) {
            $data['requested_start_at'] = Carbon::parse($data['requested_start_at']);
        }
        $data['client_id'] = $client->id;

        $onboarding = DB::transaction(function () use ($client, $clientData, $activities, $data) {
            $clientData['onboarding_step'] = 2;
            $client->update($clientData);
            $onboarding = ClientOnboarding::create($data);
            foreach ($activities as $key => $value) {
                $onboarding->activities()->attach($key, ['assistance_level' => $value]);
            }
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
