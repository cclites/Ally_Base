<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Client;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\ClientCaregiver;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClientCaregiverController extends BaseController
{
    public function index(Client $client)
    {
        $this->authorize('read', $client);

        $caregivers = $client->caregivers()->ordered()->get()->map(function ($caregiver) use ($client) {
            return (new ClientCaregiver($client, $caregiver))->toArray(null);
        });

        return $caregivers->sortBy('nameLastFirst')->values()->all();
    }

    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);
        $caregiver_id = $request->input('caregiver_id');

        $data = $request->validate([
            'caregiver_hourly_id' => 'nullable|exists:rate_codes,id',
            'caregiver_hourly_rate' => 'nullable|numeric|min:0.00|max:999.99',
            'caregiver_fixed_id' => 'nullable|exists:rate_codes,id',
            'caregiver_fixed_rate' => 'nullable|numeric|min:0.00|max:999.99',
            'client_hourly_id' => 'nullable|exists:rate_codes,id',
            'client_hourly_rate' => 'nullable|numeric|min:0.00|max:999.99',
            'client_fixed_id' => 'nullable|exists:rate_codes,id',
            'client_fixed_rate' => 'nullable|numeric|min:0.00|max:999.99',
            'provider_hourly_id' => 'nullable|exists:rate_codes,id',
            'provider_hourly_fee' => 'nullable|numeric|min:0.00|max:999.99',
            'provider_fixed_id' => 'nullable|exists:rate_codes,id',
            'provider_fixed_fee' => 'nullable|numeric|min:0.00|max:999.99',
        ]);

        if ($client->caregivers()->syncWithoutDetaching([$caregiver_id => $data])) {
            $caregiver = $client->caregivers
                ->where('id', $caregiver_id)
                ->first();

            // Append # of future shifts scheduled for this client/caregiver
            $caregiver->scheduled_shifts_count = $caregiver->schedules()->where('client_id', $client->id)
                ->forClient($client)
                ->future($client->business->timezone)
                ->count();

            $responseData = new ClientCaregiver($client, $caregiver);
            return new CreatedResponse('The caregiver assignment has been saved.', $responseData->toResponse(null));
        }

        return new ErrorResponse(500, 'Unable to save caregiver assignment.');
    }

    public function show(Client $client, Caregiver $caregiver)
    {
        $this->authorize('read', $client);

        return new ClientCaregiver($client, $caregiver);
    }

    public function potentialCaregivers(Client $client)
    {
        $this->authorize('read', $client);

        $current_caregivers = $client->caregivers()->select('caregivers.id')->pluck('id');
        $excluded_caregivers = $client->excludedCaregivers()->select('caregiver_id')->pluck('caregiver_id');
        $excluded_caregivers = $excluded_caregivers->merge($current_caregivers);

        $caregivers = Caregiver::with('businesses')
            ->forRequestedBusinesses()
            ->ordered()
            ->whereNotIn('caregivers.id', $excluded_caregivers->values())
            ->select('caregivers.id')
            ->get()
            ->map(function ($caregiver) {

                $name = $caregiver->active ? $caregiver->nameLastFirst : $caregiver->nameLastFirst . " (Inactive)";

                return [
                    'id' => $caregiver->id,
                    'name' => $name,
                    'businesses' => $caregiver->businesses->pluck('id'),
                    'active' => $caregiver->active,
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json($caregivers);
    }

    /**
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function detachCaregiver(Client $client)
    {
        $this->authorize('update', $client);

        $caregiver = Caregiver::find(request('caregiver_id'));

        if ($caregiver->isClockedIn($client->id)) {
            return new ErrorResponse(400, $caregiver->name() . ' is clocked in for this client.');
        }

        // check for scheduled shifts and or clockin
        $check = $caregiver->schedules()
            ->forClient($client)
            ->future($client->business->timezone)
            ->exists();

        if ($check) {
            $msg = $caregiver->name() . ' has future scheduled shifts for ' . $client->name() . ' and cannot be removed.';
            return new ErrorResponse(400, $msg);
        }

        $client->caregivers()->detach($caregiver->id);

        return new SuccessResponse($caregiver->name() . ' removed from ' . $client->name());
    }

    /**
     * Update rate information for future scheduled shifts for client/caregiver.
     *
     * @param \Illuminate\Http\Request $request
     * @param Client $client
     * @return ErrorResponse|SuccessResponse
     */
    public function updateScheduleRates(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate(['caregiver_id' => 'required|exists:caregivers,id']);

        $caregiver = $client->caregivers
            ->where('id', $request->caregiver_id)
            ->first();

        // Update hourly shifts
        $caregiver->schedules()
            ->where('fixed_rates', 0)
            ->forClient($client)
            ->future($client->business->timezone)
            ->update([
                'caregiver_rate' => $caregiver->pivot->caregiver_hourly_rate,
                'provider_fee' => $caregiver->pivot->provider_hourly_fee,
            ]);

        // Update daily shifts
        $caregiver->schedules()
            ->where('fixed_rates', 1)
            ->forClient($client)
            ->future($client->business->timezone)
            ->update([
                'caregiver_rate' => $caregiver->pivot->caregiver_fixed_rate,
                'provider_fee' => $caregiver->pivot->provider_fixed_fee,
            ]);

        return new SuccessResponse($caregiver->name() . "'s rate was applied to all future schedules.");
    }
}
